<?php

namespace App\Service;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Repository\HistorialPreciosRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistorialPreciosOperationsService implements HistorialPreciosOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HistorialPreciosRepository $repository
    ) {}

    public function createHistorialPrecios(HistorialPrecios $historialPrecios, Producto $producto): void
    {
        $this->validateHistorialPrecios($historialPrecios);

        // Actualizar precio del producto según el tipo
        if ($historialPrecios->getTipo() === "venta") {
            $producto->setPrecioVentaActual($historialPrecios->getPrecioNuevo());
        } else {
            $producto->setPrecioCompra($historialPrecios->getPrecioNuevo());
        }

        $this->entityManager->persist($historialPrecios);
        $this->entityManager->flush();
    }

    public function updateHistorialPrecios(HistorialPrecios $historialPrecios): void
    {
        $this->validateHistorialPrecios($historialPrecios);
        $producto = $historialPrecios->getProducto();

        // Actualizar precio del producto según el tipo
        if ($historialPrecios->getTipo() === "venta") {
            $producto->setPrecioVentaActual($historialPrecios->getPrecioNuevo());
        } else {
            $producto->setPrecioCompra($historialPrecios->getPrecioNuevo());
        }

        $this->entityManager->flush();
    }

    public function deleteHistorialPrecios(HistorialPrecios $historialPrecios): void
    {
        $producto = $historialPrecios->getProducto();
        $tipo = $historialPrecios->getTipo();

        // Encontrar el precio anterior o el penúltimo registro
        $registros = $this->repository->createQueryBuilder('h')
            ->andWhere('h.producto = :producto')
            ->andWhere('h.tipo = :tipo')
            ->setParameter('producto', $producto)
            ->setParameter('tipo', $tipo)
            ->orderBy('h.fecha_cambio', 'DESC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        if (count($registros) > 1) {
            $penultimoRegistro = $registros[1];
            $nuevoPrecio = $penultimoRegistro->getPrecioNuevo();
        } else {
            $nuevoPrecio = $historialPrecios->getPrecioAnterior();
        }

        // Revertir precio del producto
        if ($tipo === 'venta') {
            $producto->setPrecioVentaActual($nuevoPrecio);
        } else {
            $producto->setPrecioCompra($nuevoPrecio);
        }

        $this->entityManager->remove($historialPrecios);
        $this->entityManager->flush();
    }

    private function validateHistorialPrecios(HistorialPrecios $historialPrecios): void
    {
        if (empty($historialPrecios->getTipo())) {
            throw new \InvalidArgumentException('El tipo no puede estar vacío');
        }

        if ($historialPrecios->getPrecioNuevo() === null) {
            throw new \InvalidArgumentException('El precio nuevo no puede estar vacío');
        }

        if (!in_array($historialPrecios->getTipo(), ['compra', 'venta'])) {
            throw new \InvalidArgumentException('El tipo debe ser "compra" o "venta"');
        }
    }
}
