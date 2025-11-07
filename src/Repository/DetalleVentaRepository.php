<?php

namespace App\Repository;

use App\Entity\DetalleVenta;
use App\Service\DetalleVenta\Interface\DetalleVentaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DetalleVentaRepository extends ServiceEntityRepository implements DetalleVentaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetalleVenta::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('dv')
            ->leftJoin('dv.venta', 'v')
            ->leftJoin('dv.producto', 'p')
            ->andWhere('p.nombre LIKE :searchTerm OR v.id LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('dv.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
