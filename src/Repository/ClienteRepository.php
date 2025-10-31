<?php

namespace App\Repository;

use App\Entity\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    // En ClienteRepository.php
    public function findDeudaTotalByCliente(Cliente $cliente): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(venta.total), 0) as deuda_total')
            ->leftJoin('c.ventas', 'venta')
            ->where('c.id = :clienteId')
            ->andWhere('venta.estado != :estadoCompletada OR venta.estado IS NULL')
            ->setParameter('clienteId', $cliente->getId())
            ->setParameter('estadoCompletada', 'completada')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }
}
