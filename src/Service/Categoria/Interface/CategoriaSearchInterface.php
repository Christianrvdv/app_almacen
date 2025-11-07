<?php

namespace App\Service\Categoria\Interface;

use Symfony\Component\HttpFoundation\Request;

interface CategoriaSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
