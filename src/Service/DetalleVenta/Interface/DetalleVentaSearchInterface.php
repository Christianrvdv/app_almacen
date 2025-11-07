<?php

namespace App\Service\DetalleVenta\Interface;

use Symfony\Component\HttpFoundation\Request;

interface DetalleVentaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
