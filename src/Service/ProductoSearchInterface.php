<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface ProductoSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
