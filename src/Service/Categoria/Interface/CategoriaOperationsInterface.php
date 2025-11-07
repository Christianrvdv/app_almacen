<?php

namespace App\Service\Categoria\Interface;

use App\Entity\Categoria;

interface CategoriaOperationsInterface
{
    public function createCategoria(Categoria $categoria): void;
    public function updateCategoria(Categoria $categoria): void;
    public function deleteCategoria(Categoria $categoria): void;
}
