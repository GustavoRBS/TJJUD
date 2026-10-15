<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorio')]
class ReportController extends AbstractController
{
    #[Route('', name: 'relatorio_index', methods: ['GET'])]
    public function index(Connection $connection): Response
    {
        return $this->render('relatorio/index.html.twig', [
            'porAutor' => $this->carregarRelatorio($connection),
        ]);
    }

    #[Route('/pdf', name: 'relatorio_pdf', methods: ['GET'])]
    public function pdf(Connection $connection): Response
    {
        $html = $this->renderView('relatorio/pdf.html.twig', [
            'porAutor' => $this->carregarRelatorio($connection),
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

    /**
     * @return array<int, array{autor: array{codigo: int, nome: string}, livros: list<array<string, mixed>>, total: float}>
     */
    private function carregarRelatorio(Connection $connection): array
    {
        $linhas = $connection->fetchAllAssociative('SELECT * FROM vw_relatorio_livros_por_autor');

        $porAutor = [];

        foreach ($linhas as $linha) {
            $codigoAutor = (int) $linha['autor_codigo'];

            if (!isset($porAutor[$codigoAutor])) {
                $porAutor[$codigoAutor] = [
                    'autor' => [
                        'codigo' => $codigoAutor,
                        'nome' => $linha['autor_nome'],
                    ],
                    'livros' => [],
                    'total' => 0.0,
                ];
            }

            $porAutor[$codigoAutor]['livros'][] = $linha;
            $porAutor[$codigoAutor]['total'] += (float) $linha['livro_valor'];
        }

        return $porAutor;
    }
}
