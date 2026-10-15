<?php

namespace App\Tests\Controller;

use App\Factory\AssuntoFactory;
use App\Factory\LivroFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssuntoControllerTest extends WebTestCase
{
    public function testIndexListaAssuntosCadastrados(): void
    {
        $client = static::createClient();
        AssuntoFactory::createOne(['descricao' => 'Biografia']);

        $client->request('GET', '/assuntos');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Biografia');
    }

    public function testNovoCriaAssuntoComSucesso(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/assuntos/novo');

        $form = $crawler->selectButton('Salvar')->form([
            'assunto[descricao]' => 'Suspense',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/assuntos');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Suspense');
    }

    public function testNovoComDescricaoVaziaMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/assuntos/novo');

        $form = $crawler->selectButton('Salvar')->form([
            'assunto[descricao]' => '',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Informe a descrição do assunto.');
    }

    public function testNovoComDescricaoDuplicadaMostraErroDeValidacao(): void
    {
        $client = static::createClient();
        AssuntoFactory::createOne(['descricao' => 'Assunto Existente']);

        $crawler = $client->request('GET', '/assuntos/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'assunto[descricao]' => 'Assunto Existente',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Já existe um assunto cadastrado com essa descrição.');
    }

    public function testEditarAtualizaDescricaoDoAssunto(): void
    {
        $client = static::createClient();
        $assunto = AssuntoFactory::createOne(['descricao' => 'Descricao Antiga']);

        $crawler = $client->request('GET', '/assuntos/'.$assunto->getCodAs().'/editar');
        $form = $crawler->selectButton('Salvar')->form([
            'assunto[descricao]' => 'Descricao Nova',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/assuntos');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Descricao Nova');
    }

    public function testExcluirAssuntoSemVinculoRemoveComSucesso(): void
    {
        $client = static::createClient();
        $assunto = AssuntoFactory::createOne(['descricao' => 'Assunto Sem Livro']);

        $crawler = $client->request('GET', '/assuntos');
        $form = $crawler->filter('form[action="/assuntos/'.$assunto->getCodAs().'/excluir"]')->form();

        $client->submit($form);

        self::assertResponseRedirects('/assuntos');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Assunto excluído com sucesso.');
        self::assertSelectorTextNotContains('body', 'Assunto Sem Livro');
    }

    public function testExcluirAssuntoComLivroVinculadoEBloqueado(): void
    {
        $client = static::createClient();
        $assunto = AssuntoFactory::createOne(['descricao' => 'Assunto Com Livro']);
        LivroFactory::createOne(['assuntos' => [$assunto]]);

        $crawler = $client->request('GET', '/assuntos');
        $form = $crawler->filter('form[action="/assuntos/'.$assunto->getCodAs().'/excluir"]')->form();

        $client->submit($form);

        self::assertResponseRedirects('/assuntos');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Não é possível excluir "Assunto Com Livro"');
        self::assertSelectorTextContains('body', 'Assunto Com Livro');
    }
}
