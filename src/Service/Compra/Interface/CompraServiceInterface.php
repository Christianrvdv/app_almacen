<?php

namespace App\Service\Compra\Interface;

use App\Entity\Compra;
use App\Entity\Producto;

interface CompraServiceInterface
{
    public function create(Compra $compra): void;
    public function update(Compra $compra, array $originalDetalles = []): void;
    public function delete(Compra $compra): void;
    public function initialize(?Producto $producto = null): Compra;
}
