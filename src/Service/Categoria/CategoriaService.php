<?php

namespace App\Service\Categoria;

use App\Entity\Categoria;
use App\Service\Categoria\Interface\CategoriaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class CategoriaService implements CategoriaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(Categoria $categoria): void
    {
        $this->validate($categoria);
        $this->entityManager->persist($categoria);
        $this->entityManager->flush();
    }

    public function update(Categoria $categoria): void
    {
        $this->validate($categoria);
        $this->entityManager->flush();
    }

    public function delete(Categoria $categoria): void
    {
        $this->entityManager->remove($categoria);
        $this->entityManager->flush();
    }

    private function validate(Categoria $categoria): void
    {
        if (empty($categoria->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
