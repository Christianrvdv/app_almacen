<?php

namespace App\Service\Compra;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use App\Service\CommonService;
use App\Service\Compra\Interface\CompraOperationsInterface;
use App\Service\Core\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class CompraOperationsService implements CompraOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService,
        private TransactionService $transactionService
    ) {}

    public function createCompra(Compra $compra): void
    {
        $this->validateCompra($compra);
        $this->processCompraDetails($compra);

        $this->entityManager->persist($compra);
        $this->entityManager->flush();
    }

    public function updateCompra(Compra $compra, array $originalDetalles = []): void
    {
        $this->validateCompra($compra);

        if (!empty($originalDetalles)) {
            $this->transactionService->handleDetailChanges(
                $originalDetalles,
                $compra->getDetalleCompras(),
                $compra,
                'compra'
            );
        }

        $this->processCompraDetails($compra);
        $this->entityManager->flush();
    }

    public function deleteCompra(Compra $compra): void
    {
        $this->entityManager->remove($compra);
        $this->entityManager->flush();
    }

    public function initializeCompra(?Producto $producto = null): Compra
    {
        $compra = new Compra();
        $compra->setFecha($this->commonService->getCurrentDateTime());

        if ($producto) {
            $compra->setProveedor($producto->getProveedor());

            $detalleCompra = new DetalleCompra();
            $detalleCompra->setProducto($producto);
            $detalleCompra->setPrecioUnitario($producto->getPrecioCompra());
            $detalleCompra->setCantidad(0);
            $detalleCompra->setSubtotal(0);

            $compra->addDetalleCompra($detalleCompra);
        }

        return $compra;
    }

    private function validateCompra(Compra $compra): void
    {
        if (!$compra->getFecha()) {
            throw new \InvalidArgumentException('La fecha de compra es obligatoria');
        }

        if ($compra->getDetalleCompras()->isEmpty()) {
            throw new \InvalidArgumentException('La compra debe tener al menos un detalle');
        }
    }

    private function processCompraDetails(Compra $compra): void
    {
        $this->transactionService->processCompra($compra);
    }
}
