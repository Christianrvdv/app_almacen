<?php

namespace App\Service\Estadisticas\Interface;

interface EstadisticasProviderInterface
{
    public function getStatistics(string $filtro, string $fechaEspecifica): array;
}
