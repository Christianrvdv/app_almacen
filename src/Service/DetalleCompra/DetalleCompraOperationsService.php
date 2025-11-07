<?php

namespace App\Service\DetalleCompra;

use App\Entity\DetalleCompra;
use App\Service\DetalleCompra\Interface\DetalleCompraOperationsInterface;
use Doctrine\ORM\EntityManagerInterface;

class DetalleCompraOperationsService implements DetalleCompraOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createDetalleCompra(DetalleCompra $detalleCompra): void
    {
        $this->validateDetalleCompra($detalleCompra);
        $this->calculateAndSetSubtotal($detalleCompra);
        $this->entityManager->persist($detalleCompra);
        $this->entityManager->flush();
    }

    public function updateDetalleCompra(DetalleCompra $detalleCompra): void
    {
        $this->validateDetalleCompra($detalleCompra);
        $this->calculateAndSetSubtotal($detalleCompra);
        $this->entityManager->flush();
    }

    public function deleteDetalleCompra(DetalleCompra $detalleCompra): void
    {
        $this->entityManager->remove($detalleCompra);
        $this->entityManager->flush();
    }

    private function validateDetalleCompra(DetalleCompra $detalleCompra): void
    {
        if ($detalleCompra->getCantidad() <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero');
        }

        if ((float) $detalleCompra->getPrecioUnitario() <= 0) {
            throw new \InvalidArgumentException('El precio unitario debe ser mayor a cero');
        }

        if (!$detalleCompra->getProducto() && !$detalleCompra->getProductoNombreHistorico()) {
            throw new \InvalidArgumentException('Debe seleccionar un producto o proporcionar datos históricos');
        }
    }

    private function calculateAndSetSubtotal(DetalleCompra $detalleCompra): void
    {
        $subtotal = $detalleCompra->getCantidad() * (float) $detalleCompra->getPrecioUnitario();
        $detalleCompra->setSubtotal(number_format($subtotal, 2, '.', ''));
    }
}
