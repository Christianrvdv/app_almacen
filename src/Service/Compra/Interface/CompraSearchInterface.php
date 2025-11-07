<?php

namespace App\Service\Compra\Interface;

use Symfony\Component\HttpFoundation\Request;

interface CompraSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
