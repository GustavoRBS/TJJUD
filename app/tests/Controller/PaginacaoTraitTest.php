<?php

namespace App\Tests\Controller;

use App\Controller\PaginacaoTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginacaoTraitTest extends TestCase
{
    public function testPadraoQuandoNenhumParametroInformado(): void
    {
        $resultado = $this->resolver(new Request());

        self::assertSame(['pagina' => 1, 'porPagina' => 10, 'busca' => ''], $resultado);
    }

    public function testPorPaginaForaDaListaCaiNoPadrao(): void
    {
        $resultado = $this->resolver(new Request(['porPagina' => '999']));

        self::assertSame(10, $resultado['porPagina']);
    }

    public function testPorPaginaValidoEhRespeitado(): void
    {
        $resultado = $this->resolver(new Request(['porPagina' => '50']));

        self::assertSame(50, $resultado['porPagina']);
    }

    public function testPaginaMenorQueUmVira1(): void
    {
        $resultado = $this->resolver(new Request(['pagina' => '-5']));

        self::assertSame(1, $resultado['pagina']);
    }

    public function testBuscaEhAparadaDeEspacos(): void
    {
        $resultado = $this->resolver(new Request(['busca' => '  Machado  ']));

        self::assertSame('Machado', $resultado['busca']);
    }

    /**
     * @return array{pagina: int, porPagina: int, busca: string}
     */
    private function resolver(Request $request): array
    {
        $objeto = new class {
            use PaginacaoTrait;

            /**
             * @return array{pagina: int, porPagina: int, busca: string}
             */
            public function resolver(Request $request): array
            {
                return $this->resolverPaginacao($request);
            }
        };

        return $objeto->resolver($request);
    }
}
