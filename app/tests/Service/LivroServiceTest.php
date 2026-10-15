<?php

namespace App\Tests\Service;

use App\Entity\Livro;
use App\Factory\AssuntoFactory;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use App\Repository\AssuntoRepository;
use App\Repository\AutorRepository;
use App\Repository\LivroRepository;
use App\Service\LivroService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LivroServiceTest extends KernelTestCase
{
    private LivroService $livroService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->livroService = self::getContainer()->get(LivroService::class);
    }

    public function testListarRetornaEmOrdemAlfabetica(): void
    {
        LivroFactory::createOne(['titulo' => 'Zebra e o Mar']);
        LivroFactory::createOne(['titulo' => 'Aventuras de Ana']);

        $titulos = array_map(static fn (Livro $livro) => $livro->getTitulo(), $this->livroService->listar());

        self::assertSame(['Aventuras de Ana', 'Zebra e o Mar'], $titulos);
    }

    public function testCriarPersisteLivroComAutorEAssunto(): void
    {
        $autor = AutorFactory::createOne();
        $assunto = AssuntoFactory::createOne();

        $livro = new Livro();
        $livro->setTitulo('Dom Casmurro');
        $livro->setEditora('Editora Exemplo');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('1899');
        $livro->setValor('39.90');
        $livro->addAutor($autor);
        $livro->addAssunto($assunto);

        $this->livroService->criar($livro);

        self::assertNotNull($livro->getCodL());
        self::assertCount(1, $this->livroService->listar());
        self::assertCount(1, $livro->getAutores());
        self::assertCount(1, $livro->getAssuntos());
    }

    public function testAtualizarAlteraTitulo(): void
    {
        $livro = LivroFactory::createOne(['titulo' => 'Título Antigo']);

        $livro->setTitulo('Título Novo');
        $this->livroService->atualizar($livro);

        self::getContainer()->get(EntityManagerInterface::class)->clear();
        $recarregado = self::getContainer()->get(LivroRepository::class)->find($livro->getCodL());

        self::assertSame('Título Novo', $recarregado->getTitulo());
    }

    public function testExcluirRemoveLivroELimpaVinculos(): void
    {
        $autor = AutorFactory::createOne();
        $assunto = AssuntoFactory::createOne();
        $livro = LivroFactory::createOne(['autores' => [$autor], 'assuntos' => [$assunto]]);
        $id = $livro->getCodL();

        $this->livroService->excluir($livro);

        self::assertNull(self::getContainer()->get(LivroRepository::class)->find($id));
        self::assertCount(1, self::getContainer()->get(AutorRepository::class)->findAll());
        self::assertCount(1, self::getContainer()->get(AssuntoRepository::class)->findAll());
    }
}
