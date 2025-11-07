<?php

namespace App\Service\Cliente\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ClienteSearchInterface
{
    public function searchAndPaginate(Request $request): array;
}
