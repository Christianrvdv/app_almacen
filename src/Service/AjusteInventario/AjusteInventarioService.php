<?php

namespace App\Service\AjusteInventario;

use App\Entity\AjusteInventario;
use App\Service\AjusteInventario\Interface\AjusteInventarioServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class AjusteInventarioService implements AjusteInventarioServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(AjusteInventario $ajuste): void
    {
        $this->validate($ajuste);
        $this->entityManager->persist($ajuste);
        $this->entityManager->flush();
    }

    public function update(AjusteInventario $ajuste): void
    {
        $this->validate($ajuste);
        $this->entityManager->flush();
    }

    public function delete(AjusteInventario $ajuste): void
    {
        $this->entityManager->remove($ajuste);
        $this->entityManager->flush();
    }

    public function validate(AjusteInventario $ajuste): void
    {
        if ($ajuste->getCantidad() <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero');
        }

        if (empty($ajuste->getMotivo())) {
            throw new \InvalidArgumentException('El motivo no puede estar vacío');
        }

        if (empty($ajuste->getUsuario())) {
            throw new \InvalidArgumentException('El usuario no puede estar vacío');
        }

        if (!$ajuste->getProducto()) {
            throw new \InvalidArgumentException('El producto es requerido');
        }
    }
}
