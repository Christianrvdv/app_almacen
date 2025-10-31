<?php

namespace App\Repository;

use App\Entity\Compra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Compra>
 */
class CompraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compra::class);
    }

    public function findByProveedorId(int $proveedorId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.proveedor = :proveedorId')
            ->setParameter('proveedorId', $proveedorId)
            ->getQuery()
            ->getResult();
    }
}
