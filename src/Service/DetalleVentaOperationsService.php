<?php

namespace App\Service;

use App\Entity\DetalleVenta;
use Doctrine\ORM\EntityManagerInterface;

class DetalleVentaOperationsService implements DetalleVentaOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createDetalleVenta(DetalleVenta $detalleVenta): void
    {
        $this->validateDetalleVenta($detalleVenta);
        $this->calculateSubtotal($detalleVenta);
        $this->entityManager->persist($detalleVenta);
        $this->entityManager->flush();
    }

    public function updateDetalleVenta(DetalleVenta $detalleVenta): void
    {
        $this->validateDetalleVenta($detalleVenta);
        $this->calculateSubtotal($detalleVenta);
        $this->entityManager->flush();
    }

    public function deleteDetalleVenta(DetalleVenta $detalleVenta): void
    {
        $this->entityManager->remove($detalleVenta);
        $this->entityManager->flush();
    }

    private function validateDetalleVenta(DetalleVenta $detalleVenta): void
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
