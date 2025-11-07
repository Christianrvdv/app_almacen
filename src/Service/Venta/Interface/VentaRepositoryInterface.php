<?php

namespace App\Service\Venta\Interface;

interface VentaRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
    public function findByClienteId(int $clienteId): array;
}
