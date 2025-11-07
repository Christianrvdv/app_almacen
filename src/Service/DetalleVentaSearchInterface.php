<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface DetalleVentaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
