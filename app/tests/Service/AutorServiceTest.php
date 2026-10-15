<?php

namespace App\Tests\Service;

use App\Entity\Autor;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use App\Repository\AutorRepository;
use App\Service\AutorService;
use App\Service\Exception\RegraDeNegocioException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AutorServiceTest extends KernelTestCase
{
    private AutorService $autorService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->autorService = self::getContainer()->get(AutorService::class);
    }

    public function testListarRetornaEmOrdemAlfabetica(): void
    {
        AutorFactory::createOne(['nome' => 'Zuleica Autora']);
        AutorFactory::createOne(['nome' => 'Ana Autora']);

        $nomes = array_map(static fn (Autor $autor) => $autor->getNome(), $this->autorService->listar());

        self::assertSame(['Ana Autora', 'Zuleica Autora'], $nomes);
    }

    public function testCriarPersisteAutor(): void
    {
        $autor = new Autor();
        $autor->setNome('Machado de Assis');

        $this->autorService->criar($autor);

        self::assertNotNull($autor->getCodAu());
        self::assertCount(1, $this->autorService->listar());
    }

    public function testAtualizarAlteraNome(): void
    {
        $autor = AutorFactory::createOne(['nome' => 'Nome Antigo']);

        $autor->setNome('Nome Novo');
        $this->autorService->atualizar($autor);

        self::getContainer()->get(EntityManagerInterface::class)->clear();
        $recarregado = self::getContainer()->get(AutorRepository::class)->find($autor->getCodAu());

        self::assertSame('Nome Novo', $recarregado->getNome());
    }

    public function testExcluirRemoveAutorSemLivrosVinculados(): void
    {
        $autor = AutorFactory::createOne();
        $id = $autor->getCodAu();

        $this->autorService->excluir($autor);

        self::assertNull(self::getContainer()->get(AutorRepository::class)->find($id));
    }

    public function testExcluirBloqueiaAutorComLivrosVinculados(): void
    {
        $autor = AutorFactory::createOne(['nome' => 'Autor Vinculado']);
        LivroFactory::createOne(['autores' => [$autor]]);

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage('Não é possível excluir "Autor Vinculado": há 1 livro(s) vinculado(s) a este autor.');

        $this->autorService->excluir($autor);
    }
}
