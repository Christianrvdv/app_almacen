<?php

namespace App\Service\DetalleCompra\Interface;

interface DetalleCompraRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
    public function findByCompraId(int $compraId): array;
}
