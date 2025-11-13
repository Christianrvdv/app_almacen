<?php

namespace App\Service\Venta\Interface;

use Symfony\Component\HttpFoundation\Request;

interface VentaQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
