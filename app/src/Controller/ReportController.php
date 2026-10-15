<?php

namespace App\Controller;

use App\Service\RelatorioService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorio')]
class ReportController extends AbstractController
{
    public function __construct(
        private readonly RelatorioService $relatorioService,
    ) {
    }

    #[Route('', name: 'relatorio_index', methods: ['GET'])]
    public function index(): Response
    {
        $porAutor = $this->relatorioService->porAutor();

        return $this->render('relatorio/index.html.twig', [
            'porAutor' => $porAutor,
            'totalGeral' => $this->relatorioService->totalGeral($porAutor),
        ]);
    }

    #[Route('/pdf', name: 'relatorio_pdf', methods: ['GET'])]
    public function pdf(): Response
    {
        $porAutor = $this->relatorioService->porAutor();

        $html = $this->renderView('relatorio/pdf.html.twig', [
            'porAutor' => $porAutor,
            'totalGeral' => $this->relatorioService->totalGeral($porAutor),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="relatorio-livros-por-autor.pdf"',
            ]
        );
    }
}
