<?php

namespace App\Service\DetalleVenta;

use App\Entity\DetalleVenta;
use App\Service\DetalleVenta\Interface\DetalleVentaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class DetalleVentaService implements DetalleVentaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(DetalleVenta $detalleVenta): void
    {
        $this->validate($detalleVenta);
        $this->calculateSubtotal($detalleVenta);
        $this->entityManager->persist($detalleVenta);
        $this->entityManager->flush();
    }

    public function update(DetalleVenta $detalleVenta): void
    {
        $this->validate($detalleVenta);
        $this->calculateSubtotal($detalleVenta);
        $this->entityManager->flush();
    }

    public function delete(DetalleVenta $detalleVenta): void
    {
        $this->entityManager->remove($detalleVenta);
        $this->entityManager->flush();
    }

    public function validate(DetalleVenta $detalleVenta): void
    {
        if ($detalleVenta->getCantidad() <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero');
        }

        if ($detalleVenta->getPrecioUnitario() <= 0) {
            throw new \InvalidArgumentException('El precio unitario debe ser mayor a cero');
        }

        if ($detalleVenta->getProducto() === null) {
            throw new \InvalidArgumentException('El producto es requerido');
        }
    }

    private function calculateSubtotal(DetalleVenta $detalleVenta): void
    {
        $subtotal = $detalleVenta->getCantidad() * (float) $detalleVenta->getPrecioUnitario();
        $detalleVenta->setSubtotal((string) $subtotal);
    }
}
