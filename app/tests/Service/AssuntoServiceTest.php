<?php

namespace App\Tests\Service;

use App\Entity\Assunto;
use App\Factory\AssuntoFactory;
use App\Factory\LivroFactory;
use App\Repository\AssuntoRepository;
use App\Service\AssuntoService;
use App\Service\Exception\RegraDeNegocioException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssuntoServiceTest extends KernelTestCase
{
    private AssuntoService $assuntoService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->assuntoService = self::getContainer()->get(AssuntoService::class);
    }

    public function testListarRetornaEmOrdemAlfabetica(): void
    {
        AssuntoFactory::createOne(['descricao' => 'Terror']);
        AssuntoFactory::createOne(['descricao' => 'Culinária']);

        $descricoes = array_map(static fn (Assunto $assunto) => $assunto->getDescricao(), $this->assuntoService->listar());

        self::assertSame(['Culinária', 'Terror'], $descricoes);
    }

    public function testCriarPersisteAssunto(): void
    {
        $assunto = new Assunto();
        $assunto->setDescricao('Fantasia');

        $this->assuntoService->criar($assunto);

        self::assertNotNull($assunto->getCodAs());
        self::assertCount(1, $this->assuntoService->listar());
    }

    public function testAtualizarAlteraDescricao(): void
    {
        $assunto = AssuntoFactory::createOne(['descricao' => 'Romance']);

        $assunto->setDescricao('Aventura');
        $this->assuntoService->atualizar($assunto);

        self::getContainer()->get(EntityManagerInterface::class)->clear();
        $recarregado = self::getContainer()->get(AssuntoRepository::class)->find($assunto->getCodAs());

        self::assertSame('Aventura', $recarregado->getDescricao());
    }

    public function testExcluirRemoveAssuntoSemLivrosVinculados(): void
    {
        $assunto = AssuntoFactory::createOne();
        $id = $assunto->getCodAs();

        $this->assuntoService->excluir($assunto);

        self::assertNull(self::getContainer()->get(AssuntoRepository::class)->find($id));
    }

    public function testExcluirBloqueiaAssuntoComLivrosVinculados(): void
    {
        $assunto = AssuntoFactory::createOne(['descricao' => 'Assunto Vinculado']);
        LivroFactory::createOne(['assuntos' => [$assunto]]);

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage('Não é possível excluir "Assunto Vinculado": há 1 livro(s) vinculado(s) a este assunto.');

        $this->assuntoService->excluir($assunto);
    }
}
