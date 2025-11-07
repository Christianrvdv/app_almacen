<?php

namespace App\Service\Venta\Interface;

use App\Entity\Venta;
use Doctrine\Common\Collections\ArrayCollection;

interface VentaOperationsInterface
{
    public function createVenta(Venta $venta): void;
    public function updateVenta(Venta $venta, ArrayCollection $originalDetalles): void;
    public function deleteVenta(Venta $venta): void;
    public function initializeVenta(): Venta;
}
