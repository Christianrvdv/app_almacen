<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface VentaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
