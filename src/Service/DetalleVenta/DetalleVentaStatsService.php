<?php

namespace App\Service\DetalleVenta;

use App\Repository\DetalleVentaRepository;
use App\Service\DetalleVenta\Interface\DetalleVentaStatsInterface;

class DetalleVentaStatsService implements DetalleVentaStatsInterface
{
    public function __construct(
        private DetalleVentaRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalDetalles = $this->repository->count([]);

        $totalVentasConProducto = $this->repository->createQueryBuilder('dv')
            ->select('COUNT(DISTINCT dv.venta)')
            ->where('dv.producto IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $ingresosTotales = $this->repository->createQueryBuilder('dv')
            ->select('COALESCE(SUM(dv.subtotal), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalDetalles' => $totalDetalles,
            'totalVentasConProducto' => $totalVentasConProducto,
            'ingresosTotales' => $ingresosTotales,
        ];
    }
}
