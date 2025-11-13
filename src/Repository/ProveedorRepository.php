<?php

namespace App\Repository;

use App\Entity\Proveedor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProveedorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proveedor::class);
    }

    public function findByClienteId(int $clienteId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->getQuery()
            ->getResult();
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nombre LIKE :searchTerm OR p.telefono LIKE :searchTerm OR p.email LIKE :searchTerm OR p.direccion LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
