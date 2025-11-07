<?php

namespace App\Service\DetalleCompra\Interface;

use App\Entity\DetalleCompra;

interface DetalleCompraOperationsInterface
{
    public function createDetalleCompra(DetalleCompra $detalleCompra): void;
    public function updateDetalleCompra(DetalleCompra $detalleCompra): void;
    public function deleteDetalleCompra(DetalleCompra $detalleCompra): void;
}
