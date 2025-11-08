<?php

namespace App\Service\DetalleCompra;

use App\Entity\DetalleCompra;
use App\Service\DetalleCompra\Interface\DetalleCompraServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class DetalleCompraService implements DetalleCompraServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(DetalleCompra $detalleCompra): void
    {
        $this->validate($detalleCompra);
        $this->calculateAndSetSubtotal($detalleCompra);
        $this->entityManager->persist($detalleCompra);
        $this->entityManager->flush();
    }

    public function update(DetalleCompra $detalleCompra): void
    {
        $this->validate($detalleCompra);
        $this->calculateAndSetSubtotal($detalleCompra);
        $this->entityManager->flush();
    }

    public function delete(DetalleCompra $detalleCompra): void
    {
        $this->entityManager->remove($detalleCompra);
        $this->entityManager->flush();
    }

    public function validate(DetalleCompra $detalleCompra): void
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
