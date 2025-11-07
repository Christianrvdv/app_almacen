<?php

namespace App\Service\HistorialPrecios;

use App\Repository\HistorialPreciosRepository;
use App\Service\HistorialPrecios\Interface\HistorialPreciosStatsInterface;

class HistorialPreciosStatsService implements HistorialPreciosStatsInterface
{
    public function __construct(
        private HistorialPreciosRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalRegistros = $this->repository->count([]);
        $totalVenta = $this->repository->count(['tipo' => 'venta']);
        $totalCompra = $this->repository->count(['tipo' => 'compra']);
        $totalAjustePromo = $this->repository->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where("h.tipo IN ('promocion', 'ajuste')")
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalRegistros' => $totalRegistros,
            'totalVenta' => $totalVenta,
            'totalCompra' => $totalCompra,
            'totalAjustePromo' => $totalAjustePromo,
        ];
    }
}
