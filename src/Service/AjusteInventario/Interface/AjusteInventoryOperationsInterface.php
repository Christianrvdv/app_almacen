<?php

namespace App\Service\AjusteInventario\Interface;

use App\Entity\AjusteInventario;

interface AjusteInventoryOperationsInterface
{
    public function createAjuste(AjusteInventario $ajuste): void;
    public function updateAjuste(AjusteInventario $ajuste): void;
    public function deleteAjuste(AjusteInventario $ajuste): void;
}
