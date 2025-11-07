<?php

namespace App\Service\Producto;

use App\Entity\Producto;
use App\Service\CommonService;
use App\Service\Producto\Interface\ProductoOperationsInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductoOperationsService implements ProductoOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService
    ) {}

    public function createProducto(Producto $producto): void
    {
        $this->validateProducto($producto);
        $currentDateTime = $this->commonService->getCurrentDateTime();
        $producto->setFechaCreaccion($currentDateTime);
        $producto->setFechaActualizacion($currentDateTime);

        $this->entityManager->persist($producto);
        $this->entityManager->flush();
    }

    public function updateProducto(Producto $producto): void
    {
        $this->validateProducto($producto);
        $producto->setFechaActualizacion($this->commonService->getCurrentDateTime());

        $this->entityManager->flush();
    }

    public function deleteProducto(Producto $producto): void
    {
        // Eliminar registros relacionados
        foreach ($producto->getHistorialPrecios() as $historial) {
            $this->entityManager->remove($historial);
        }

        foreach ($producto->getDetalleCompras() as $detalleCompra) {
            $this->entityManager->remove($detalleCompra);
        }

        foreach ($producto->getDetalleVentas() as $detalleVenta) {
            $this->entityManager->remove($detalleVenta);
        }

        foreach ($producto->getAjusteInventarios() as $ajuste) {
            $this->entityManager->remove($ajuste);
        }

        $this->entityManager->remove($producto);
        $this->entityManager->flush();
    }

    private function validateProducto(Producto $producto): void
    {
        if (empty($producto->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
