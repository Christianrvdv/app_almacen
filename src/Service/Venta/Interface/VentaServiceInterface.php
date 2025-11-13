<?php

namespace App\Service\Venta\Interface;

use App\Entity\Venta;
use Doctrine\Common\Collections\ArrayCollection;

interface VentaServiceInterface
{
    public function create(Venta $venta): void;
    public function update(Venta $venta, ArrayCollection $originalDetalles): void;
    public function delete(Venta $venta): void;
    public function validate(Venta $venta): void;
    public function initializeVenta(): Venta;
}
