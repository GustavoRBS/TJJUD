<?php

namespace App\Tests\Service;

use App\Factory\AssuntoFactory;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use App\Service\RelatorioService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RelatorioServiceTest extends KernelTestCase
{
    private RelatorioService $relatorioService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->relatorioService = self::getContainer()->get(RelatorioService::class);
    }

    public function testPorAutorSemDadosRetornaVazio(): void
    {
        self::assertSame([], $this->relatorioService->porAutor());
    }

    public function testPorAutorAgrupaLivroComMultiplosAutoresSobCadaUm(): void
    {
        $autor1 = AutorFactory::createOne(['nome' => 'Ana Autora']);
        $autor2 = AutorFactory::createOne(['nome' => 'Bruno Autor']);
        LivroFactory::createOne(['titulo' => 'Livro Compartilhado', 'autores' => [$autor1, $autor2]]);

        $grupos = array_values($this->relatorioService->porAutor());

        self::assertCount(2, $grupos);
        self::assertSame('Ana Autora', $grupos[0]['autor']['nome']);
        self::assertSame('Bruno Autor', $grupos[1]['autor']['nome']);
        self::assertSame('Livro Compartilhado', $grupos[0]['livros'][0]['livro_titulo']);
        self::assertSame('Livro Compartilhado', $grupos[1]['livros'][0]['livro_titulo']);
    }

    public function testPorAutorConcatenaAssuntosDoLivro(): void
    {
        $autor = AutorFactory::createOne();
        $drama = AssuntoFactory::createOne(['descricao' => 'Drama']);
        $romance = AssuntoFactory::createOne(['descricao' => 'Romance']);
        LivroFactory::createOne(['autores' => [$autor], 'assuntos' => [$romance, $drama]]);

        $grupos = array_values($this->relatorioService->porAutor());

        self::assertCount(1, $grupos[0]['livros']);
        self::assertSame('Drama, Romance', $grupos[0]['livros'][0]['livro_assuntos']);
    }

    public function testPorAutorSomaSubtotalEmDecimalExato(): void
    {
        $autor = AutorFactory::createOne();
        LivroFactory::createOne(['valor' => '0.10', 'autores' => [$autor]]);
        LivroFactory::createOne(['valor' => '0.20', 'autores' => [$autor]]);

        $grupos = array_values($this->relatorioService->porAutor());

        self::assertSame('0.30', $grupos[0]['total']);
    }

    public function testTotalGeralSomaSubtotaisDeTodosOsAutores(): void
    {
        $autor1 = AutorFactory::createOne();
        $autor2 = AutorFactory::createOne();
        LivroFactory::createOne(['valor' => '10.00', 'autores' => [$autor1]]);
        LivroFactory::createOne(['valor' => '5.50', 'autores' => [$autor2]]);

        $porAutor = $this->relatorioService->porAutor();

        self::assertSame('15.50', $this->relatorioService->totalGeral($porAutor));
    }

    public function testTotalGeralSemDadosRetornaZero(): void
    {
        self::assertSame('0.00', $this->relatorioService->totalGeral([]));
    }
}
