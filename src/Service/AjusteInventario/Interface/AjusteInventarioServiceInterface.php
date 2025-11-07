<?php

namespace App\Service\AjusteInventario\Interface;

use App\Entity\AjusteInventario;

interface AjusteInventarioServiceInterface
{
    public function create(AjusteInventario $ajuste): void;
    public function update(AjusteInventario $ajuste): void;
    public function delete(AjusteInventario $ajuste): void;
    public function validate(AjusteInventario $ajuste): void;
}
