<?php

namespace App\Service\HistorialPrecios\Interface;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;

interface HistorialPreciosOperationsInterface
{
    public function createHistorialPrecios(HistorialPrecios $historialPrecios, Producto $producto): void;
    public function updateHistorialPrecios(HistorialPrecios $historialPrecios): void;
    public function deleteHistorialPrecios(HistorialPrecios $historialPrecios): void;
}
