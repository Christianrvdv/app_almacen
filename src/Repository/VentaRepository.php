<?php

namespace App\Repository;

use App\Entity\Venta;
use App\Service\VentaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VentaRepository extends ServiceEntityRepository implements VentaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venta::class);
    }

    public function findByClienteId(int $clienteId): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->getQuery()
            ->getResult();
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->leftJoin('v.cliente', 'c')
            ->orderBy('v.fecha', 'DESC');

        if (is_numeric($searchTerm)) {
            $queryBuilder
                ->andWhere('v.id = :id OR v.estado LIKE :searchTerm OR v.total LIKE :searchTerm OR v.tipo_venta LIKE :searchTerm OR c.nombre LIKE :searchTerm')
                ->setParameter('id', (int) $searchTerm)
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        } else {
            $queryBuilder
                ->andWhere('v.estado LIKE :searchTerm OR v.tipo_venta LIKE :searchTerm OR c.nombre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
