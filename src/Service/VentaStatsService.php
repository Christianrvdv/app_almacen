<?php

namespace App\Service;

use App\Repository\VentaRepository;

class VentaStatsService implements VentaStatsInterface
{
    public function __construct(
        private VentaRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalVentas = $this->repository->count([]);
        $totalCompletadas = $this->repository->count(['estado' => 'completada']);
        $totalPendientes = $this->repository->count(['estado' => 'pendiente']);

        $totalIngresos = $this->repository->createQueryBuilder('v')
            ->select('SUM(v.total)')
            ->where('v.estado = :estado')
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'totalVentas' => $totalVentas,
            'totalCompletadas' => $totalCompletadas,
            'totalPendientes' => $totalPendientes,
            'totalIngresos' => $totalIngresos,
        ];
    }
}
