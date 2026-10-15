<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Form\LivroType;
use App\Service\Exception\RegraDeNegocioException;
use App\Service\LivroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livros')]
class LivroController extends AbstractController
{
    use PaginacaoTrait;

    #[Route('', name: 'livro_index', methods: ['GET'])]
    public function index(Request $request, LivroService $livroService): Response
    {
        $paginacao = $this->resolverPaginacao($request);
        $resultado = $livroService->paginar($paginacao['pagina'], $paginacao['porPagina'], $paginacao['busca']);

        return $this->render('livro/index.html.twig', [
            'livros' => $resultado['itens'],
            'paginaAtual' => $resultado['paginaAtual'],
            'porPagina' => $resultado['porPagina'],
            'total' => $resultado['total'],
            'totalPaginas' => $resultado['totalPaginas'],
            'busca' => $resultado['busca'],
        ]);
    }

    #[Route('/novo', name: 'livro_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LivroService $livroService): Response
    {
        $livro = new Livro();
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livroService->criar($livro);

            $this->addFlash('success', sprintf('Livro "%s" cadastrado com sucesso.', $livro->getTitulo()));

            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/new.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'livro_show', methods: ['GET'])]
    public function show(Livro $livro): Response
    {
        return $this->render('livro/show.html.twig', [
            'livro' => $livro,
        ]);
    }

    #[Route('/{id}/editar', name: 'livro_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livro $livro, LivroService $livroService): Response
    {
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livroService->atualizar($livro);

            $this->addFlash('success', 'Livro atualizado com sucesso.');

            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/edit.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/excluir', name: 'livro_delete', methods: ['POST'])]
    public function delete(Request $request, Livro $livro, LivroService $livroService): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$livro->getCodI(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de segurança inválido. Tente novamente.');

            return $this->redirectToRoute('livro_index');
        }

        try {
            $livroService->excluir($livro);
            $this->addFlash('success', 'Livro excluído com sucesso.');
        } catch (RegraDeNegocioException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('livro_index');
    }
}
