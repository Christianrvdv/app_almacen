<?php

namespace App\Service\DetalleCompra;

use App\Service\DetalleCompra\Interface\DetalleCompraRepositoryInterface;
use App\Service\DetalleCompra\Interface\DetalleCompraStatsInterface;

class DetalleCompraStatsService implements DetalleCompraStatsInterface
{
    public function __construct(
        private DetalleCompraRepositoryInterface $repository
    ) {}

    public function getStatistics(): array
    {
        $totalDetalles = $this->repository->count([]);

        $totalConProducto = $this->repository->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.producto IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $sumaSubtotal = $this->repository->createQueryBuilder('d')
            ->select('SUM(d.subtotal)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalDetalles' => $totalDetalles,
            'totalConProducto' => $totalConProducto,
            'sumaSubtotal' => $sumaSubtotal ?? 0,
        ];
    }
}
