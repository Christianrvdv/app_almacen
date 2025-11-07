<?php

namespace App\Service\Venta;

use App\Entity\Venta;
use App\Service\CommonService;
use App\Service\Core\TransactionService;
use App\Service\Venta\Interface\VentaOperationsInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class VentaOperationsService implements VentaOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService,
        private TransactionService $transactionService
    ) {}

    public function createVenta(Venta $venta): void
    {
        $this->validateVenta($venta);
        $this->processVentaDetails($venta);

        $this->entityManager->persist($venta);
        $this->entityManager->flush();
    }

    public function updateVenta(Venta $venta, ArrayCollection $originalDetalles): void
    {
        $this->validateVenta($venta);

        if (!$originalDetalles->isEmpty()) {
            $this->transactionService->handleDetailChanges(
                $originalDetalles,
                $venta->getDetalleVentas(),
                $venta,
                'venta'
            );
        }

        $this->processVentaDetails($venta);
        $this->entityManager->flush();
    }

    public function deleteVenta(Venta $venta): void
    {
        $this->entityManager->remove($venta);
        $this->entityManager->flush();
    }

    public function initializeVenta(): Venta
    {
        $venta = new Venta();
        $venta->setFecha($this->commonService->getCurrentDateTime());
        return $venta;
    }

    private function validateVenta(Venta $venta): void
    {
        if (!$venta->getFecha()) {
            throw new \InvalidArgumentException('La fecha de venta es obligatoria');
        }

        if ($venta->getDetalleVentas()->isEmpty()) {
            throw new \InvalidArgumentException('La venta debe tener al menos un detalle');
        }
    }

    private function processVentaDetails(Venta $venta): void
    {
        $this->transactionService->processVenta($venta);
    }
}
