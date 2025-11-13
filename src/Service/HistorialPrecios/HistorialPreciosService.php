<?php

namespace App\Service\HistorialPrecios;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Repository\HistorialPreciosRepository;
use App\Service\HistorialPrecios\Interface\HistorialPreciosServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class HistorialPreciosService implements HistorialPreciosServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HistorialPreciosRepository $repository
    ) {}

    public function create(HistorialPrecios $historialPrecios): void
    {
        $this->validate($historialPrecios);
        $this->updateProductPrice($historialPrecios);
        $this->entityManager->persist($historialPrecios);
        $this->entityManager->flush();
    }

    public function update(HistorialPrecios $historialPrecios): void
    {
        $this->validate($historialPrecios);
        $this->updateProductPrice($historialPrecios);
        $this->entityManager->flush();
    }

    public function delete(HistorialPrecios $historialPrecios): void
    {
        $this->revertProductPrice($historialPrecios);
        $this->entityManager->remove($historialPrecios);
        $this->entityManager->flush();
    }

    public function validate(HistorialPrecios $historialPrecios): void
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

        if (!$historialPrecios->getProducto()) {
            throw new \InvalidArgumentException('El producto es requerido');
        }
    }

    private function updateProductPrice(HistorialPrecios $historialPrecios): void
    {
        $producto = $historialPrecios->getProducto();

        if ($historialPrecios->getTipo() === "venta") {
            $producto->setPrecioVentaActual($historialPrecios->getPrecioNuevo());
        } else {
            $producto->setPrecioCompra($historialPrecios->getPrecioNuevo());
        }
    }

    private function revertProductPrice(HistorialPrecios $historialPrecios): void
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
    }
}
