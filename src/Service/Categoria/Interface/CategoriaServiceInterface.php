<?php

namespace App\Service\Categoria\Interface;

use App\Entity\Categoria;

interface CategoriaServiceInterface
{
    public function create(Categoria $categoria): void;
    public function update(Categoria $categoria): void;
    public function delete(Categoria $categoria): void;
}
