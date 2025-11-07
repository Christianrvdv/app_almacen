<?php

namespace App\Repository;

use App\Entity\AjusteInventario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AjusteInventario>
 */
class AjusteInventarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AjusteInventario::class);
    }

    public function findWithSearchTerm(string $searchTerm = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.producto', 'p')
            ->addSelect('p')
            ->orderBy('a.fecha', 'DESC');

        if ($searchTerm) {
            $qb->andWhere('a.motivo LIKE :searchTerm OR a.tipo LIKE :searchTerm OR a.usuario LIKE :searchTerm OR p.nombre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
