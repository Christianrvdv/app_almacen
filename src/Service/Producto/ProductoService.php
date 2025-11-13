<?php

namespace App\Service\Producto;

use App\Entity\Producto;
use App\Service\CommonService;
use App\Service\Producto\Interface\ProductoServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductoService implements ProductoServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService
    ) {}

    public function create(Producto $producto): void
    {
        $this->validate($producto);
        $currentDateTime = $this->commonService->getCurrentDateTime();
        $producto->setFechaCreaccion($currentDateTime);
        $producto->setFechaActualizacion($currentDateTime);

        $this->entityManager->persist($producto);
        $this->entityManager->flush();
    }

    public function update(Producto $producto): void
    {
        $this->validate($producto);
        $producto->setFechaActualizacion($this->commonService->getCurrentDateTime());

        $this->entityManager->flush();
    }

    public function delete(Producto $producto): void
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

    public function validate(Producto $producto): void
    {
        if (empty($producto->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }
}
