<?php

namespace App\Service\Proveedor\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ProveedorSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
