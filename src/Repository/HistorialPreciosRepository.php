<?php

namespace App\Repository;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistorialPreciosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistorialPrecios::class);
    }

    public function findLastByProductAndType(Producto $producto, string $type): ?HistorialPrecios
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.producto = :producto')
            ->andWhere('h.tipo = :type')
            ->setParameter('producto', $producto)
            ->setParameter('type', $type)
            ->orderBy('h.fecha_cambio', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.producto', 'p')
            ->andWhere('h.tipo LIKE :searchTerm OR h.motivo LIKE :searchTerm OR p.nombre LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('h.fecha_cambio', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
