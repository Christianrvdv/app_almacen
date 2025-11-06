<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface ProveedorSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
