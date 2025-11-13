<?php

namespace App\Service\Proveedor\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ProveedorQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
