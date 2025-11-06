<?php

namespace App\Service;

interface ProductoRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
    public function getIngresosPorCategoria(int $categoriaId): float;
}
