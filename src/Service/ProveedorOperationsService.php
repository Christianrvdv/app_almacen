<?php

namespace App\Service;

use App\Entity\Proveedor;
use Doctrine\ORM\EntityManagerInterface;

class ProveedorOperationsService implements ProveedorOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createProveedor(Proveedor $proveedor): void
    {
        $this->validateProveedor($proveedor);
        $this->entityManager->persist($proveedor);
        $this->entityManager->flush();
    }

    public function updateProveedor(Proveedor $proveedor): void
    {
        $this->validateProveedor($proveedor);
        $this->entityManager->flush();
    }

    public function deleteProveedor(Proveedor $proveedor): void
    {
        $this->entityManager->remove($proveedor);
        $this->entityManager->flush();
    }

    private function validateProveedor(Proveedor $proveedor): void
    {
        if (empty($proveedor->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
