<?php

namespace App\Tests\Controller;

use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AutorControllerTest extends WebTestCase
{
    public function testIndexListaAutoresCadastrados(): void
    {
        $client = static::createClient();
        AutorFactory::createOne(['nome' => 'Clarice Lispector']);

        $client->request('GET', '/autores');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Clarice Lispector');
    }

    public function testIndexComBuscaFiltraPorNome(): void
    {
        $client = static::createClient();
        AutorFactory::createOne(['nome' => 'Machado de Assis']);
        AutorFactory::createOne(['nome' => 'Clarice Lispector']);

        $client->request('GET', '/autores?busca=Machado');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Machado de Assis');
        self::assertSelectorTextNotContains('body', 'Clarice Lispector');
    }

    public function testIndexComBuscaSemResultadoMostraMensagem(): void
    {
        $client = static::createClient();
        AutorFactory::createOne(['nome' => 'Machado de Assis']);

        $client->request('GET', '/autores?busca=NomeQueNaoExiste');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Nenhum resultado encontrado para');
    }

    public function testIndexRespeitaPorPagina(): void
    {
        $client = static::createClient();
        for ($i = 1; $i <= 3; $i++) {
            AutorFactory::createOne(['nome' => "Autor Paginado {$i}"]);
        }

        $crawler = $client->request('GET', '/autores?porPagina=10');

        self::assertResponseIsSuccessful();
        self::assertCount(3, $crawler->filter('tbody tr'));
    }

    public function testNovoCriaAutorComSucesso(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/autores/novo');

        $form = $crawler->selectButton('Salvar')->form([
            'autor[nome]' => 'Novo Autor de Teste',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/autores');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Novo Autor de Teste');
    }

    public function testNovoComNomeVazioMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/autores/novo');

        $form = $crawler->selectButton('Salvar')->form([
            'autor[nome]' => '',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Informe o nome do autor.');
    }

    public function testNovoComNomeDuplicadoMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        AutorFactory::createOne(['nome' => 'Autor Existente']);

        $crawler = $client->request('GET', '/autores/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'autor[nome]' => 'Autor Existente',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Já existe um autor cadastrado com esse nome.');
    }

    public function testEditarAtualizaNomeDoAutor(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne(['nome' => 'Nome Antigo']);

        $crawler = $client->request('GET', '/autores/'.$autor->getCodAu().'/editar');
        $form = $crawler->selectButton('Salvar')->form([
            'autor[nome]' => 'Nome Atualizado',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/autores');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Nome Atualizado');
    }

    public function testExcluirAutorSemVinculoRemoveComSucesso(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne(['nome' => 'Autor Sem Livro']);

        $crawler = $client->request('GET', '/autores');
        $form = $crawler->filter('form[action="/autores/'.$autor->getCodAu().'/excluir"]')->form();

        $client->submit($form);

        self::assertResponseRedirects('/autores');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor excluído com sucesso.');
        self::assertSelectorTextNotContains('body', 'Autor Sem Livro');
    }

    public function testExcluirAutorComLivroVinculadoEBloqueado(): void
    {
        $client = static::createClient();
        $autor = AutorFactory::createOne(['nome' => 'Autor Com Livro']);
        LivroFactory::createOne(['autores' => [$autor]]);

        $crawler = $client->request('GET', '/autores');
        $form = $crawler->filter('form[action="/autores/'.$autor->getCodAu().'/excluir"]')->form();

        $client->submit($form);

        self::assertResponseRedirects('/autores');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Não é possível excluir "Autor Com Livro"');
        self::assertSelectorTextContains('body', 'Autor Com Livro');
    }
}
