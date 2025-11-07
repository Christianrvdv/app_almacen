<?php

namespace App\Service\Compra;

use App\Repository\CompraRepository;
use App\Service\Compra\Interface\CompraStatsInterface;

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
