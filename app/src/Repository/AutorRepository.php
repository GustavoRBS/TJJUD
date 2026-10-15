<?php

namespace App\Repository;

use App\Entity\Autor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Autor>
 */
class AutorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Autor::class);
    }

    /**
     * @return array{itens: list<Autor>, total: int}
     */
    public function paginar(int $pagina, int $porPagina, string $busca = ''): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.nome', 'ASC');

        if ($busca !== '') {
            $qb->andWhere('a.nome LIKE :busca')
                ->setParameter('busca', '%'.$busca.'%');
        }

        $total = (int) (clone $qb)
            ->select('COUNT(a.codAu)')
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
