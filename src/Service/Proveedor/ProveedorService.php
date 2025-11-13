<?php

namespace App\Service\Proveedor;

use App\Entity\Proveedor;
use App\Service\Proveedor\Interface\ProveedorServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProveedorService implements ProveedorServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(Proveedor $proveedor): void
    {
        $this->validate($proveedor);
        $this->entityManager->persist($proveedor);
        $this->entityManager->flush();
    }

    public function update(Proveedor $proveedor): void
    {
        $this->validate($proveedor);
        $this->entityManager->flush();
    }

    public function delete(Proveedor $proveedor): void
    {
        $this->entityManager->remove($proveedor);
        $this->entityManager->flush();
    }

    public function validate(Proveedor $proveedor): void
    {
        if (empty($proveedor->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
