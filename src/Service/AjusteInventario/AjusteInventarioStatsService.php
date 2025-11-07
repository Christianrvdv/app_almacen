<?php

namespace App\Service\AjusteInventario;

use App\Repository\AjusteInventarioRepository;

class AjusteInventarioStatsService
{
    public function __construct(
        private AjusteInventarioRepository $repository
    ) {}

    public function getStatistics(): array
    {
        return [
            'totalAjustes' => $this->repository->count([]),
            'totalEntradas' => $this->repository->count(['tipo' => 'entrada']),
            'totalSalidas' => $this->repository->count(['tipo' => 'salida']),
            'cantidadUsuariosUnicos' => $this->getUniqueUsersCount()
        ];
    }

    private function getUniqueUsersCount(): int
    {
        return $this->repository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.usuario)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
