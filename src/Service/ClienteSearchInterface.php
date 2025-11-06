<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface ClienteSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
