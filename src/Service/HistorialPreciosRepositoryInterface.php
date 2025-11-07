<?php

namespace App\Service;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;

interface HistorialPreciosRepositoryInterface
{
    public function findBySearchTerm(string $searchTerm): array;
    public function count(array $criteria = []): int;
    public function createQueryBuilder(string $alias);
    public function findLastByProductAndType(Producto $producto, string $type): ?HistorialPrecios;
}
