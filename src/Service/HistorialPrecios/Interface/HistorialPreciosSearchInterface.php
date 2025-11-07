<?php

namespace App\Service\HistorialPrecios\Interface;

use Symfony\Component\HttpFoundation\Request;

interface HistorialPreciosSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
