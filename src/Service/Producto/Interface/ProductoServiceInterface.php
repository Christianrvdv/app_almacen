<?php

namespace App\Service\Producto\Interface;

use App\Entity\Producto;

interface ProductoServiceInterface
{
    public function create(Producto $producto): void;
    public function update(Producto $producto): void;
    public function delete(Producto $producto): void;
    public function validate(Producto $producto): void;
}
