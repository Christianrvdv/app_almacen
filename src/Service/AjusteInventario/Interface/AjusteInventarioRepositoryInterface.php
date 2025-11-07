<?php

namespace App\Service\AjusteInventario\Interface;

interface AjusteInventarioRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
}
