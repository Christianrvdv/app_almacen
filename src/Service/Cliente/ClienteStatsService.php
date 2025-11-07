<?php

namespace App\Service\Cliente;

use App\Repository\ClienteRepository;
use App\Service\Cliente\Interface\ClienteStatsInterface;

class ClienteStatsService implements ClienteStatsInterface
{
    public function __construct(
        private ClienteRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalClientes = $this->repository->count([]);

        $totalConEmail = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $totalConTelefono = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $totalConDireccion = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalClientes' => $totalClientes,
            'totalConEmail' => $totalConEmail,
            'totalConTelefono' => $totalConTelefono,
            'totalConDireccion' => $totalConDireccion,
        ];
    }
}
