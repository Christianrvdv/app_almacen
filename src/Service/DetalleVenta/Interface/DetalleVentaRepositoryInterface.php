<?php

namespace App\Service\DetalleVenta\Interface;

interface DetalleVentaRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
}
