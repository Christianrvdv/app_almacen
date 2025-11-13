<?php

namespace App\Service\Proveedor\Interface;

use App\Entity\Proveedor;

interface ProveedorServiceInterface
{
    public function create(Proveedor $proveedor): void;
    public function update(Proveedor $proveedor): void;
    public function delete(Proveedor $proveedor): void;
    public function validate(Proveedor $proveedor): void;
}
