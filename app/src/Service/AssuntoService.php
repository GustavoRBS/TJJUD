<?php

namespace App\Service;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;
use App\Service\Exception\RegraDeNegocioException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AssuntoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AssuntoRepository $assuntoRepository,
    ) {
    }

    /**
     * @return Assunto[]
     */
    public function listar(): array
    {
        return $this->assuntoRepository->findBy([], ['descricao' => 'ASC']);
    }

    /**
     * @return array{itens: list<Assunto>, paginaAtual: int, porPagina: int, total: int, totalPaginas: int, busca: string}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $resultado = $this->assuntoRepository->paginar($pagina, $porPagina, $busca);

        return [
            'itens' => $resultado['itens'],
            'paginaAtual' => $pagina,
            'porPagina' => $porPagina,
            'total' => $resultado['total'],
            'totalPaginas' => max(1, (int) ceil($resultado['total'] / $porPagina)),
            'busca' => $busca,
        ];
    }

    public function criar(Assunto $assunto): void
    {
        $this->em->persist($assunto);
        $this->em->flush();
    }

    public function atualizar(Assunto $assunto): void
    {
        $this->em->flush();
    }

    /**
     * @throws RegraDeNegocioException se o assunto ainda tiver livros vinculados
     */
    public function excluir(Assunto $assunto): void
    {
        if (!$assunto->getLivros()->isEmpty()) {
            throw new RegraDeNegocioException(sprintf(
                'Não é possível excluir "%s": há %d livro(s) vinculado(s) a este assunto. Remova o vínculo nos livros primeiro.',
                $assunto->getDescricao(),
                $assunto->getLivros()->count(),
            ));
        }

        try {
            $this->em->remove($assunto);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            throw new RegraDeNegocioException('Não foi possível excluir este assunto devido a vínculos existentes no banco de dados.');
        }
    }
}
