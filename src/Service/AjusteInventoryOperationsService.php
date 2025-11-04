<?php

namespace App\Service;

use App\Entity\AjusteInventario;
use Doctrine\ORM\EntityManagerInterface;

class AjusteInventoryOperationsService implements AjusteInventoryOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createAjuste(AjusteInventario $ajuste): void
    {
        $this->validateAjuste($ajuste);
        $this->entityManager->persist($ajuste);
        $this->entityManager->flush();
    }

    public function updateAjuste(AjusteInventario $ajuste): void
    {
        $this->validateAjuste($ajuste);
        $this->entityManager->flush();
    }

    public function deleteAjuste(AjusteInventario $ajuste): void
    {
        $this->entityManager->remove($ajuste);
        $this->entityManager->flush();
    }

    private function validateAjuste(AjusteInventario $ajuste): void
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
