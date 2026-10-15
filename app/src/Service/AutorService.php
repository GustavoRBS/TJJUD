<?php

namespace App\Service;

use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Service\Exception\RegraDeNegocioException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AutorService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AutorRepository $autorRepository,
    ) {
    }

    /**
     * @return Autor[]
     */
    public function listar(): array
    {
        return $this->autorRepository->findBy([], ['nome' => 'ASC']);
    }

    /**
     * @return array{itens: list<Autor>, paginaAtual: int, porPagina: int, total: int, totalPaginas: int, busca: string}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $resultado = $this->autorRepository->paginar($pagina, $porPagina, $busca);

        return [
            'itens' => $resultado['itens'],
            'paginaAtual' => $pagina,
            'porPagina' => $porPagina,
            'total' => $resultado['total'],
            'totalPaginas' => max(1, (int) ceil($resultado['total'] / $porPagina)),
            'busca' => $busca,
        ];
    }

    public function criar(Autor $autor): void
    {
        $this->em->persist($autor);
        $this->em->flush();
    }

    public function atualizar(Autor $autor): void
    {
        $this->em->flush();
    }

    /**
     * @throws RegraDeNegocioException se o autor ainda tiver livros vinculados
     */
    public function excluir(Autor $autor): void
    {
        if (!$autor->getLivros()->isEmpty()) {
            throw new RegraDeNegocioException(sprintf(
                'Não é possível excluir "%s": há %d livro(s) vinculado(s) a este autor. Remova o vínculo nos livros primeiro.',
                $autor->getNome(),
                $autor->getLivros()->count(),
            ));
        }

        try {
            $this->em->remove($autor);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            throw new RegraDeNegocioException('Não foi possível excluir este autor devido a vínculos existentes no banco de dados.');
        }
    }
}
