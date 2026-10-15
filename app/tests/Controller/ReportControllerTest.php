<?php

namespace App\Tests\Controller;

use App\Factory\AssuntoFactory;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testIndexSemDadosMostraMensagemVazia(): void
    {
        $client = static::createClient();

        $client->request('GET', '/relatorio');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Nenhum dado disponível ainda.');
    }

    public function testIndexAgrupaLivroComMultiplosAutoresSobCadaUm(): void
    {
        $client = static::createClient();

        $autor1 = AutorFactory::createOne(['nome' => 'Autor Um']);
        $autor2 = AutorFactory::createOne(['nome' => 'Autor Dois']);
        $assunto = AssuntoFactory::createOne(['descricao' => 'Assunto Teste']);

        LivroFactory::createOne([
            'titulo' => 'Livro Compartilhado',
            'valor' => '50.00',
            'autores' => [$autor1, $autor2],
            'assuntos' => [$assunto],
        ]);

        $client->request('GET', '/relatorio');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Autor Um');
        self::assertSelectorTextContains('body', 'Autor Dois');

        $conteudo = (string) $client->getResponse()->getContent();
        self::assertSame(2, substr_count($conteudo, 'Livro Compartilhado'));
    }

    public function testIndexCalculaSubtotalPorAutor(): void
    {
        $client = static::createClient();

        $autor = AutorFactory::createOne(['nome' => 'Autor Com Dois Livros']);
        LivroFactory::createOne(['titulo' => 'Primeiro Livro', 'valor' => '30.00', 'autores' => [$autor]]);
        LivroFactory::createOne(['titulo' => 'Segundo Livro', 'valor' => '20.00', 'autores' => [$autor]]);

        $client->request('GET', '/relatorio');

        self::assertResponseIsSuccessful();
     
        self::assertSelectorTextContains('body', "R$\u{a0}50,00");
    }

    public function testPdfRetornaDocumentoValido(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();
        LivroFactory::createOne(['autores' => [$autor]]);

        $client->request('GET', '/relatorio/pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', (string) $client->getResponse()->getContent());
    }
}
