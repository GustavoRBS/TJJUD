<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class RelatorioService
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array<int, array{autor: array{codigo: int, nome: string}, livros: list<array<string, mixed>>, total: string}>
     */
    public function porAutor(): array
    {
        $linhas = $this->connection->fetchAllAssociative('SELECT * FROM vw_relatorio_livros_por_autor');

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
                    'total' => '0.00',
                ];
            }

            $porAutor[$codigoAutor]['livros'][] = $linha;
            $porAutor[$codigoAutor]['total'] = bcadd($porAutor[$codigoAutor]['total'], (string) $linha['livro_valor'], 2);
        }

        return $porAutor;
    }

    /**
     * @param array<int, array{total: string}> $porAutor
     */
    public function totalGeral(array $porAutor): string
    {
        $total = '0.00';

        foreach ($porAutor as $grupo) {
            $total = bcadd($total, $grupo['total'], 2);
        }

        return $total;
    }
}
