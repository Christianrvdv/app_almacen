<?php

namespace App\Repository;

use App\Entity\AjusteInventario;
use App\Service\AjusteInventarioRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AjusteInventarioRepository extends ServiceEntityRepository implements AjusteInventarioRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AjusteInventario::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.producto', 'p')
            ->andWhere('a.motivo LIKE :searchTerm OR a.tipo LIKE :searchTerm OR a.usuario LIKE :searchTerm OR p.nombre LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('a.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
