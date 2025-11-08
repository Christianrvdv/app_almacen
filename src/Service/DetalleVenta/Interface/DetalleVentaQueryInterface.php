<?php

namespace App\Service\DetalleVenta\Interface;

use Symfony\Component\HttpFoundation\Request;

interface DetalleVentaQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
