<?php

namespace App\Service\DetalleCompra\Interface;

use App\Entity\DetalleCompra;

interface DetalleCompraServiceInterface
{
    public function create(DetalleCompra $detalleCompra): void;
    public function update(DetalleCompra $detalleCompra): void;
    public function delete(DetalleCompra $detalleCompra): void;
    public function validate(DetalleCompra $detalleCompra): void;
}
