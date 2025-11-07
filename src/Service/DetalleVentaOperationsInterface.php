<?php

namespace App\Service;

use App\Entity\DetalleVenta;

interface DetalleVentaOperationsInterface
{
    public function createDetalleVenta(DetalleVenta $detalleVenta): void;
    public function updateDetalleVenta(DetalleVenta $detalleVenta): void;
    public function deleteDetalleVenta(DetalleVenta $detalleVenta): void;
}
