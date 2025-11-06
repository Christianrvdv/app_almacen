<?php

namespace App\Service;

use App\Repository\ProveedorRepository;

class ProveedorStatsService implements ProveedorStatsInterface
{
    public function __construct(
        private ProveedorRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalProveedores = $this->repository->count([]);
        $totalConTelefono = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConEmail = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConDireccion = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalProveedores' => $totalProveedores,
            'totalConTelefono' => $totalConTelefono,
            'totalConEmail' => $totalConEmail,
            'totalConDireccion' => $totalConDireccion,
        ];
    }
}
