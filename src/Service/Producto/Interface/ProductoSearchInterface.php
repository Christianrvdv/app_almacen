<?php

namespace App\Service\Producto\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ProductoSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
