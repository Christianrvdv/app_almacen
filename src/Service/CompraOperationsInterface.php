<?php

namespace App\Service;

use App\Entity\Compra;
use App\Entity\Producto;
use Symfony\Component\HttpFoundation\Request;

interface CompraOperationsInterface
{
    public function createCompra(Compra $compra): void;
    public function updateCompra(Compra $compra, array $originalDetalles = []): void;
    public function deleteCompra(Compra $compra): void;
    public function initializeCompra(?Producto $producto = null): Compra;
}
