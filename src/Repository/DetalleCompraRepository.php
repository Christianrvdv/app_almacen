<?php

namespace App\Repository;

use App\Entity\DetalleCompra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<DetalleCompra>
 */
class DetalleCompraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetalleCompra::class);
    }

    public function findByCompraId(int $compraId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.compra = :compraId')
            ->setParameter('compraId', $compraId)
            ->getQuery()
            ->getResult();
    }
}
