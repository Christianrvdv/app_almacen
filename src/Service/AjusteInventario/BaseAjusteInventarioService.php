<?php

namespace App\Service\AjusteInventario;

use App\Entity\AjusteInventario;

abstract class BaseAjusteInventarioService
{
    abstract public function execute(AjusteInventario $ajuste): void;

    protected function validateCantidad(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero');
        }
    }
}
