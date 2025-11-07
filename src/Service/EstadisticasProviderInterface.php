<?php

namespace App\Service;

interface EstadisticasProviderInterface
{
    public function getStatistics(string $filtro, string $fechaEspecifica): array;
}
