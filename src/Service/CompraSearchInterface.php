<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface CompraSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
