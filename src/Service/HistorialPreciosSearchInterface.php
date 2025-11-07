<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface HistorialPreciosSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
