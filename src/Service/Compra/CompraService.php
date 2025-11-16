<?php

namespace App\Service\Compra;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use App\Service\CommonService;
use App\Service\Compra\Interface\CompraServiceInterface;
use App\Service\Core\TransactionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class CompraService implements CompraServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService,
        private TransactionService $transactionService
    ) {}

    public function create(Compra $compra): void
    {
        $this->validate($compra);
        $this->processCompraDetails($compra);

        $this->entityManager->persist($compra);
        $this->entityManager->flush();
    }

    public function update(Compra $compra, ArrayCollection $originalDetalles = null): void
    {
        $this->validate($compra);

        if ($originalDetalles !== null && !$originalDetalles->isEmpty()) {
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

    public function delete(Compra $compra): void
    {
        $this->entityManager->remove($compra);
        $this->entityManager->flush();
    }

    public function initialize(?Producto $producto = null): Compra
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

    private function validate(Compra $compra): void
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
