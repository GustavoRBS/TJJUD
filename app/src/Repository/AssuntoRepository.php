<?php

namespace App\Repository;

use App\Entity\Assunto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assunto>
 */
class AssuntoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assunto::class);
    }

    /**
     * @return array{itens: list<Assunto>, total: int}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.descricao', 'ASC');

        if ($busca !== '') {
            $qb->andWhere('a.descricao LIKE :busca')
                ->setParameter('busca', '%'.$busca.'%');
        }

        $total = (int) (clone $qb)
            ->select('COUNT(a.codAs)')
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
