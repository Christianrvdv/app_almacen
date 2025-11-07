<?php

namespace App\Service\Compra\Interface;

use App\Entity\Compra;
use App\Entity\Producto;

interface CompraOperationsInterface
{
    public function createCompra(Compra $compra): void;
    public function updateCompra(Compra $compra, array $originalDetalles = []): void;
    public function deleteCompra(Compra $compra): void;
    public function initializeCompra(?Producto $producto = null): Compra;
}
