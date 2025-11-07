<?php

namespace App\Service\Producto\Interface;

use App\Entity\Producto;

interface ProductoOperationsInterface
{
    public function createProducto(Producto $producto): void;
    public function updateProducto(Producto $producto): void;
    public function deleteProducto(Producto $producto): void;
}
