<?php
// src/Service/EstadisticasService.php

namespace App\Service;

class EstadisticasService implements EstadisticasProviderInterface
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function getStatistics(string $filtro, string $fechaEspecifica): array
    {
        return $this->statisticsService->getDashboardStatistics($filtro, $fechaEspecifica);
    }
}
