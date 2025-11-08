<?php

namespace App\Service\Cliente\Interface;

use Symfony\Component\HttpFoundation\Request;

interface ClienteQueryInterface
{
    public function searchAndPaginate(Request $request): array;
    public function getStatistics(): array;
}
