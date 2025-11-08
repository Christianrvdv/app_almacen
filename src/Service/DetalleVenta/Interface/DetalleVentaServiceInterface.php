<?php

namespace App\Service\DetalleVenta\Interface;

use App\Entity\DetalleVenta;

interface DetalleVentaServiceInterface
{
    public function create(DetalleVenta $detalleVenta): void;
    public function update(DetalleVenta $detalleVenta): void;
    public function delete(DetalleVenta $detalleVenta): void;
    public function validate(DetalleVenta $detalleVenta): void;
}
