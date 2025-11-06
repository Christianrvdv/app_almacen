<?php

namespace App\Service;

use App\Entity\Proveedor;

interface ProveedorOperationsInterface
{
    public function createProveedor(Proveedor $proveedor): void;
    public function updateProveedor(Proveedor $proveedor): void;
    public function deleteProveedor(Proveedor $proveedor): void;
}
