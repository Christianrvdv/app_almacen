<?php

namespace App\Service\Core;

use App\Entity\Compra;
use App\Entity\Venta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Procesa los detalles de una compra/venta y calcula subtotales
     */
    public function processTransactionDetails($transaction, string $detailType = 'compra'): float
    {
        $total = 0;
        $details = [];

        if ($detailType === 'compra') {
            $details = $transaction->getDetalleCompras();
        } else {
            $details = $transaction->getDetalleVentas();
        }

        foreach ($details as $detalle) {
            $subtotal = $detalle->getCantidad() * $detalle->getPrecioUnitario();
            $detalle->setSubtotal($subtotal);
            $total += $subtotal;

            // Persistir el detalle si es nuevo
            if (!$detalle->getId()) {
                $this->entityManager->persist($detalle);
            }
        }

        // Actualizar el total de la transacción
        if (method_exists($transaction, 'setTotal')) {
            $transaction->setTotal($total);
        }

        return $total;
    }

    /**
     * Procesa una compra completa
     */
    public function processCompra(Compra $compra): void
    {
        $this->processTransactionDetails($compra, 'compra');
    }

    /**
     * Procesa una venta completa (incluyendo actualización del cliente)
     */
    public function processVenta(Venta $venta): void
    {
        $totalVenta = $this->processTransactionDetails($venta, 'venta');

        // Actualizar total de compras del cliente
        $cliente = $venta->getCliente();
        if ($cliente) {
            $totalCliente = (float) $cliente->getCompraTotales() + $totalVenta;
            $cliente->setCompraTotales($totalCliente);
        }
    }

    /**
     * Maneja los cambios en los detalles durante la edición
     */
    public function handleDetailChanges(
        ArrayCollection $originalDetails,
        Collection $currentDetails,
        object $parentEntity,
        string $detailType = 'compra'
    ): void {
        $setterMethod = $detailType === 'compra' ? 'setCompra' : 'setVenta';

        // Agregar nuevos detalles
        foreach ($currentDetails as $detail) {
            if (!$originalDetails->contains($detail)) {
                $detail->$setterMethod($parentEntity);
                $this->entityManager->persist($detail);
            }
        }

        // Eliminar detalles removidos
        foreach ($originalDetails as $detail) {
            if (!$currentDetails->contains($detail)) {
                $this->entityManager->remove($detail);
            }
        }
    }
}
