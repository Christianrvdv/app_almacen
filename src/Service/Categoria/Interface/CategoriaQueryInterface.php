<?php

namespace App\Service\Categoria\Interface;

use Symfony\Component\HttpFoundation\Request;

interface CategoriaQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
