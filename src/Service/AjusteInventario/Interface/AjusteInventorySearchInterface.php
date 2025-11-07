<?php

namespace App\Service\AjusteInventario\Interface;

use Symfony\Component\HttpFoundation\Request;

interface AjusteInventorySearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
