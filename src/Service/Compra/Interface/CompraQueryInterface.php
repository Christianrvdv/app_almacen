<?php

namespace App\Service\Compra\Interface;

use Symfony\Component\HttpFoundation\Request;

interface CompraQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
