<?php

namespace App\Repository;

use App\Entity\Venta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Venta>
 */
class VentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venta::class);
    }

    // Si tienes métodos que filtran por IDs, deben actualizarse
    public function findByClienteId(int $clienteId): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->getQuery()
            ->getResult();
    }
}
