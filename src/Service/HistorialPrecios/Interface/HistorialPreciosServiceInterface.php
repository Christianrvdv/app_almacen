<?php

namespace App\Service\HistorialPrecios\Interface;

use App\Entity\HistorialPrecios;

interface HistorialPreciosServiceInterface
{
    public function create(HistorialPrecios $historialPrecios): void;
    public function update(HistorialPrecios $historialPrecios): void;
    public function delete(HistorialPrecios $historialPrecios): void;
    public function validate(HistorialPrecios $historialPrecios): void;
}
