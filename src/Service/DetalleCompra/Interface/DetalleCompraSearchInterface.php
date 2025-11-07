<?php

namespace App\Service\DetalleCompra\Interface;

use Symfony\Component\HttpFoundation\Request;

interface DetalleCompraSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
