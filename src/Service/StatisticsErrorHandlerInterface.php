<?php
// src/Service/StatisticsErrorHandlerInterface.php

namespace App\Service;

use Psr\Log\LoggerInterface;

interface StatisticsErrorHandlerInterface
{
    public function handleError(\Exception $e, string $filtro, string $fechaEspecifica): void;
}
