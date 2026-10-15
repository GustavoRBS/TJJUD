<?php

namespace App\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Service\Exception\RegraDeNegocioException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class LivroService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LivroRepository $livroRepository,
    ) {
    }

    /**
     * @return Livro[]
     */
    public function listar(): array
    {
        return $this->livroRepository->findBy([], ['titulo' => 'ASC']);
    }

    /**
     * @return array{itens: list<Livro>, paginaAtual: int, porPagina: int, total: int, totalPaginas: int, busca: string}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $resultado = $this->livroRepository->paginar($pagina, $porPagina, $busca);

        return [
            'itens' => $resultado['itens'],
            'paginaAtual' => $pagina,
            'porPagina' => $porPagina,
            'total' => $resultado['total'],
            'totalPaginas' => max(1, (int) ceil($resultado['total'] / $porPagina)),
            'busca' => $busca,
        ];
    }

    public function criar(Livro $livro): void
    {
        $this->em->persist($livro);
        $this->em->flush();
    }

    public function atualizar(Livro $livro): void
    {
        $this->em->flush();
    }

    /**
     * @throws RegraDeNegocioException se houver vínculos impedindo a exclusão
     */
    public function excluir(Livro $livro): void
    {
        try {
            $this->em->remove($livro);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            throw new RegraDeNegocioException('Não foi possível excluir este livro devido a vínculos existentes no banco de dados.');
        }
    }
}
