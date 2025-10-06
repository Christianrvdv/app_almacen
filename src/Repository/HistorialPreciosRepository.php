<?php

namespace App\Repository;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistorialPrecios>
 */
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

}
