<?php

namespace App\Service\Producto\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ProductoQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
