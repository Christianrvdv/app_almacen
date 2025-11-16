<?php

namespace App\Service\Compra\Interface;

use App\Entity\Compra;
use App\Entity\Producto;
use Doctrine\Common\Collections\ArrayCollection;

interface CompraServiceInterface
{
    public function create(Compra $compra): void;
    public function update(Compra $compra, ArrayCollection $originalDetalles = null): void;
    public function delete(Compra $compra): void;
    public function initialize(?Producto $producto = null): Compra;
}
