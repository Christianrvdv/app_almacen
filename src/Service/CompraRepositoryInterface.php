<?php

namespace App\Service;

interface CompraRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
    public function findByProveedorId(int $proveedorId): array;
}
