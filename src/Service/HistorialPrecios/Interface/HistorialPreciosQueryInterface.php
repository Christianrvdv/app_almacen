<?php

namespace App\Service\HistorialPrecios\Interface;

use Symfony\Component\HttpFoundation\Request;

interface HistorialPreciosQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
    public function findLastByProductAndType($producto, string $type): ?object;
}
