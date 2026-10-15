<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livro>
 */
class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }

    /**
     * @return array{itens: list<Livro>, total: int}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $qb = $this->createQueryBuilder('l')
            ->orderBy('l.titulo', 'ASC');

        if ($busca !== '') {
            $qb->andWhere('l.titulo LIKE :busca OR l.editora LIKE :busca')
                ->setParameter('busca', '%'.$busca.'%');
        }

        $total = (int) (clone $qb)
            ->select('COUNT(l.codI)')
            ->getQuery()
            ->getSingleScalarResult();

        $itens = $qb
            ->setFirstResult(($pagina - 1) * $porPagina)
            ->setMaxResults($porPagina)
            ->getQuery()
            ->getResult();

        return ['itens' => $itens, 'total' => $total];
    }
}
