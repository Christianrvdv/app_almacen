<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface AjusteInventorySearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
