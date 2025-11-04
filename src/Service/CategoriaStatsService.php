<?php

namespace App\Service;

use App\Repository\CategoriaRepository;

class CategoriaStatsService implements CategoriaStatsInterface
{
    public function __construct(
        private CategoriaRepository $repository
    ) {}

    public function getStatistics(): array
    {
        $totalCategorias = $this->repository->count([]);
        $totalConDescripcion = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.descripcion IS NOT NULL AND c.descripcion != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
        $totalEnUso = $this->repository->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.productos', 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalCategorias' => $totalCategorias,
            'totalConDescripcion' => $totalConDescripcion,
            'totalEnUso' => $totalEnUso,
        ];
    }
}
