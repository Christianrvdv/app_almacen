<?php

namespace App\Service\AjusteInventario\Interface;

use Symfony\Component\HttpFoundation\Request;

interface AjusteInventarioQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
