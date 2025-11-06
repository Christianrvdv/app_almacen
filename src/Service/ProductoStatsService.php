<?php

namespace App\Service;

use App\Repository\ProductoRepository;

class ProductoStatsService implements ProductoStatsInterface
{
    public function __construct(
        private ProductoRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalProductos = $this->repository->count([]);
        $totalActivos = $this->repository->count(['activo' => true]);
        $totalInactivos = $this->repository->count(['activo' => false]);
        $totalConCategoria = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.categoria IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalProductos' => $totalProductos,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'totalConCategoria' => $totalConCategoria,
        ];
    }
}
