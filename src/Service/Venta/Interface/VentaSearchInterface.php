<?php

namespace App\Service\Venta\Interface;

use Symfony\Component\HttpFoundation\Request;

interface VentaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
