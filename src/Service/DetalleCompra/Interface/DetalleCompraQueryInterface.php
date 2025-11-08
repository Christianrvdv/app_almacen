<?php

namespace App\Service\DetalleCompra\Interface;

use Symfony\Component\HttpFoundation\Request;

interface DetalleCompraQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
