<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Form\AssuntoType;
use App\Service\AssuntoService;
use App\Service\Exception\RegraDeNegocioException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assuntos')]
class AssuntoController extends AbstractController
{
    use PaginacaoTrait;

    #[Route('', name: 'assunto_index', methods: ['GET'])]
    public function index(Request $request, AssuntoService $assuntoService): Response
    {
        $paginacao = $this->resolverPaginacao($request);
        $resultado = $assuntoService->paginar($paginacao['pagina'], $paginacao['porPagina'], $paginacao['busca']);

        return $this->render('assunto/index.html.twig', [
            'assuntos' => $resultado['itens'],
            'paginaAtual' => $resultado['paginaAtual'],
            'porPagina' => $resultado['porPagina'],
            'total' => $resultado['total'],
            'totalPaginas' => $resultado['totalPaginas'],
            'busca' => $resultado['busca'],
        ]);
    }

    #[Route('/novo', name: 'assunto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AssuntoService $assuntoService): Response
    {
        $assunto = new Assunto();
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assuntoService->criar($assunto);

            $this->addFlash('success', sprintf('Assunto "%s" cadastrado com sucesso.', $assunto->getDescricao()));

            return $this->redirectToRoute('assunto_index');
        }

        return $this->render('assunto/new.html.twig', [
            'assunto' => $assunto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'assunto_show', methods: ['GET'])]
    public function show(Assunto $assunto): Response
    {
        return $this->render('assunto/show.html.twig', [
            'assunto' => $assunto,
        ]);
    }

    #[Route('/{id}/editar', name: 'assunto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assunto $assunto, AssuntoService $assuntoService): Response
    {
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assuntoService->atualizar($assunto);

            $this->addFlash('success', 'Assunto atualizado com sucesso.');

            return $this->redirectToRoute('assunto_index');
        }

        return $this->render('assunto/edit.html.twig', [
            'assunto' => $assunto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/excluir', name: 'assunto_delete', methods: ['POST'])]
    public function delete(Request $request, Assunto $assunto, AssuntoService $assuntoService): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$assunto->getCodAs(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de segurança inválido. Tente novamente.');

            return $this->redirectToRoute('assunto_index');
        }

        try {
            $assuntoService->excluir($assunto);
            $this->addFlash('success', 'Assunto excluído com sucesso.');
        } catch (RegraDeNegocioException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('assunto_index');
    }
}
