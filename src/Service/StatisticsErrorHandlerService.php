<?php
// src/Service/StatisticsErrorHandlerService.php

namespace App\Service;

use Psr\Log\LoggerInterface;

class StatisticsErrorHandlerService implements StatisticsErrorHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handleError(\Exception $e, string $filtro, string $fechaEspecifica): void
    {
        $this->logger->error('Error cargando estadísticas: ' . $e->getMessage(), [
            'filtro' => $filtro,
            'fecha_especifica' => $fechaEspecifica,
            'trace' => $e->getTraceAsString()
        ]);
    }
}
