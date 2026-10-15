<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Service\AutorService;
use App\Service\Exception\RegraDeNegocioException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/autores')]
class AutorController extends AbstractController
{
    use PaginacaoTrait;

    #[Route('', name: 'autor_index', methods: ['GET'])]
    public function index(Request $request, AutorService $autorService): Response
    {
        $paginacao = $this->resolverPaginacao($request);
        $resultado = $autorService->paginar($paginacao['pagina'], $paginacao['porPagina'], $paginacao['busca']);

        return $this->render('autor/index.html.twig', [
            'autores' => $resultado['itens'],
            'paginaAtual' => $resultado['paginaAtual'],
            'porPagina' => $resultado['porPagina'],
            'total' => $resultado['total'],
            'totalPaginas' => $resultado['totalPaginas'],
            'busca' => $resultado['busca'],
        ]);
    }

    #[Route('/novo', name: 'autor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AutorService $autorService): Response
    {
        $autor = new Autor();
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $autorService->criar($autor);

                $this->addFlash('success', sprintf('Autor "%s" cadastrado com sucesso.', $autor->getNome()));

                return $this->redirectToRoute('autor_index');
            } catch (RegraDeNegocioException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('autor/new.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'autor_show', methods: ['GET'])]
    public function show(Autor $autor): Response
    {
        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
        ]);
    }

    #[Route('/{id}/editar', name: 'autor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Autor $autor, AutorService $autorService): Response
    {
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $autorService->atualizar($autor);

                $this->addFlash('success', 'Autor atualizado com sucesso.');

                return $this->redirectToRoute('autor_index');
            } catch (RegraDeNegocioException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/excluir', name: 'autor_delete', methods: ['POST'])]
    public function delete(Request $request, Autor $autor, AutorService $autorService): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$autor->getCodAu(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de segurança inválido. Tente novamente.');

            return $this->redirectToRoute('autor_index');
        }

        try {
            $autorService->excluir($autor);
            $this->addFlash('success', 'Autor excluído com sucesso.');
        } catch (RegraDeNegocioException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('autor_index');
    }
}
