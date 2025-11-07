<?php

namespace App\Repository;

use App\Entity\DetalleCompra;
use App\Service\DetalleCompraRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetalleCompra>
 */
class DetalleCompraRepository extends ServiceEntityRepository implements DetalleCompraRepositoryInterface
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

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.compra', 'c')
            ->leftJoin('d.producto', 'p')
            ->andWhere('p.nombre LIKE :searchTerm OR c.codigo LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
