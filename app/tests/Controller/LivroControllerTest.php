<?php

namespace App\Tests\Controller;

use App\Factory\AssuntoFactory;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivroControllerTest extends WebTestCase
{
    public function testIndexListaLivrosCadastrados(): void
    {
        $client = static::createClient();
        LivroFactory::createOne(['titulo' => 'Dom Casmurro']);

        $client->request('GET', '/livros');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Dom Casmurro');
    }

    public function testNovoCriaLivroComSucesso(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();
        $assunto = AssuntoFactory::createOne();

        $crawler = $client->request('GET', '/livros/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Memórias Póstumas',
            'livro[editora]' => 'Editora Exemplo',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '1881',
            'livro[valor]' => '39.90',
            'livro[autores]' => [(string) $autor->getCodAu()],
            'livro[assuntos]' => [(string) $assunto->getCodAs()],
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/livros');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Memórias Póstumas');
    }

    public function testNovoSemAutorMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/livros/novo');

        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Livro Sem Autor',
            'livro[editora]' => 'Editora Exemplo',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '2024',
            'livro[valor]' => '10.00',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Selecione pelo menos um autor.');
    }

    public function testNovoComAnoInvalidoMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();

        $crawler = $client->request('GET', '/livros/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Livro Com Ano Invalido',
            'livro[editora]' => 'Editora Exemplo',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '24',
            'livro[valor]' => '10.00',
            'livro[autores]' => [(string) $autor->getCodAu()],
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Informe um ano com 4 dígitos');
    }

    public function testNovoComValorNegativoMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();

        $crawler = $client->request('GET', '/livros/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Livro Com Valor Negativo',
            'livro[editora]' => 'Editora Exemplo',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '2024',
            'livro[valor]' => '-10.00',
            'livro[autores]' => [(string) $autor->getCodAu()],
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'O valor não pode ser negativo.');
    }

    public function testIndexComBuscaFiltraPorTituloOuEditora(): void
    {
        $client = static::createClient();
        LivroFactory::createOne(['titulo' => 'Dom Casmurro', 'editora' => 'Editora X']);
        LivroFactory::createOne(['titulo' => 'Outro Livro', 'editora' => 'Casmurro Press']);
        LivroFactory::createOne(['titulo' => 'Totalmente Diferente', 'editora' => 'Editora Y']);

        $client->request('GET', '/livros?busca=Casmurro');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Dom Casmurro');
        self::assertSelectorTextContains('body', 'Outro Livro');
        self::assertSelectorTextNotContains('body', 'Totalmente Diferente');
    }

    public function testNovoComTituloDuplicadoMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();
        LivroFactory::createOne(['titulo' => 'Livro Existente', 'autores' => [$autor]]);

        $crawler = $client->request('GET', '/livros/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Livro Existente',
            'livro[editora]' => 'Editora Exemplo',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '2024',
            'livro[valor]' => '10.00',
            'livro[autores]' => [(string) $autor->getCodAu()],
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Já existe um livro cadastrado com esse título.');
    }

    public function testEditarAtualizaTituloDoLivro(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();
        $livro = LivroFactory::createOne(['titulo' => 'Título Antigo', 'autores' => [$autor]]);

        $crawler = $client->request('GET', '/livros/'.$livro->getCodI().'/editar');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Título Atualizado',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/livros');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Título Atualizado');
    }

    public function testExcluirLivroRemoveComSucesso(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne();
        $livro = LivroFactory::createOne(['titulo' => 'Livro A Excluir', 'autores' => [$autor]]);

        $crawler = $client->request('GET', '/livros');
        $form = $crawler->filter('form[action="/livros/'.$livro->getCodI().'/excluir"]')->form();

        $client->submit($form);

        self::assertResponseRedirects('/livros');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Livro excluído com sucesso.');
        self::assertSelectorTextNotContains('body', 'Livro A Excluir');
    }
}
