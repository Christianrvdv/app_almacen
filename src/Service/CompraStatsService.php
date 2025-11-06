<?php

namespace App\Service;

use App\Repository\CompraRepository;

class CompraStatsService implements CompraStatsInterface
{
    public function __construct(
        private CompraRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalCompras = $this->repository->count([]);
        $totalPagadas = $this->repository->count(['estado' => 'pagada']);
        $totalPendientes = $this->repository->count(['estado' => 'pendiente']);

        $gastosTotales = $this->repository->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->where('c.estado = :estado')
            ->setParameter('estado', 'pagada')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'totalCompras' => $totalCompras,
            'totalPagadas' => $totalPagadas,
            'totalPendientes' => $totalPendientes,
            'gastosTotales' => $gastosTotales,
        ];
    }
}
