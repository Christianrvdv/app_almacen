<?php

namespace App\Service\Venta;

use App\Entity\Venta;
use App\Service\CommonService;
use App\Service\Core\TransactionService;
use App\Service\Venta\Interface\VentaServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class VentaService implements VentaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService,
        private TransactionService $transactionService
    ) {}

    public function create(Venta $venta): void
    {
        $this->validate($venta);
        $this->processVentaDetails($venta);

        $this->entityManager->persist($venta);
        $this->entityManager->flush();
    }

    public function update(Venta $venta, ArrayCollection $originalDetalles): void
    {
        $this->validate($venta);

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

    public function delete(Venta $venta): void
    {
        $this->entityManager->remove($venta);
        $this->entityManager->flush();
    }

    public function validate(Venta $venta): void
    {
        if (!$venta->getFecha()) {
            throw new \InvalidArgumentException('La fecha de venta es obligatoria');
        }

        if ($venta->getDetalleVentas()->isEmpty()) {
            throw new \InvalidArgumentException('La venta debe tener al menos un detalle');
        }
    }

    public function initializeVenta(): Venta
    {
        $venta = new Venta();
        $venta->setFecha($this->commonService->getCurrentDateTime());
        return $venta;
    }

    private function processVentaDetails(Venta $venta): void
    {
        $this->transactionService->processVenta($venta);
    }
}
