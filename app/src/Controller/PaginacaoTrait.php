<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

trait PaginacaoTrait
{
    private const OPCOES_POR_PAGINA = [10, 20, 50];

    /**
     * @return array{pagina: int, porPagina: int, busca: string}
     */
    private function resolverPaginacao(Request $request): array
    {
        $porPagina = $request->query->getInt('porPagina', self::OPCOES_POR_PAGINA[0]);
        if (!in_array($porPagina, self::OPCOES_POR_PAGINA, true)) {
            $porPagina = self::OPCOES_POR_PAGINA[0];
        }

        $pagina = max(1, $request->query->getInt('pagina', 1));
        $busca = trim((string) $request->query->get('busca', ''));

        return ['pagina' => $pagina, 'porPagina' => $porPagina, 'busca' => $busca];
    }
}
