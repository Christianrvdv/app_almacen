<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface CategoriaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
