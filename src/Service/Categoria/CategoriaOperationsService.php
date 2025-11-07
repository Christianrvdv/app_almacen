<?php

namespace App\Service\Categoria;

use App\Entity\Categoria;
use App\Service\Categoria\Interface\CategoriaOperationsInterface;
use Doctrine\ORM\EntityManagerInterface;

class CategoriaOperationsService implements CategoriaOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createCategoria(Categoria $categoria): void
    {
        $this->validateCategoria($categoria);
        $this->entityManager->persist($categoria);
        $this->entityManager->flush();
    }

    public function updateCategoria(Categoria $categoria): void
    {
        $this->validateCategoria($categoria);
        $this->entityManager->flush();
    }

    public function deleteCategoria(Categoria $categoria): void
    {
        $this->entityManager->remove($categoria);
        $this->entityManager->flush();
    }

    private function validateCategoria(Categoria $categoria): void
    {
        if (empty($categoria->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
