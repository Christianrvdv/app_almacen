<?php
// src/Service/StatisticsErrorHandlerInterface.php

namespace App\Service\Estadisticas\Interface;

interface StatisticsErrorHandlerInterface
{
    public function handleError(\Exception $e, string $filtro, string $fechaEspecifica): void;
}
