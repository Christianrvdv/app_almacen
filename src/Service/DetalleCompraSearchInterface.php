<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface DetalleCompraSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
