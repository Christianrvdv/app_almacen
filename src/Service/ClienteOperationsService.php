<?php

namespace App\Service;

use App\Entity\Cliente;
use Doctrine\ORM\EntityManagerInterface;

class ClienteOperationsService implements ClienteOperationsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService
    ) {}

    public function createCliente(Cliente $cliente): void
    {
        $this->validateCliente($cliente);
        $this->setDefaultValues($cliente);

        $this->entityManager->persist($cliente);
        $this->entityManager->flush();
    }

    public function updateCliente(Cliente $cliente): void
    {
        $this->validateCliente($cliente);
        $this->entityManager->flush();
    }

    public function deleteCliente(Cliente $cliente): void
    {
        $this->entityManager->remove($cliente);
        $this->entityManager->flush();
    }

    private function validateCliente(Cliente $cliente): void
    {
        if (empty($cliente->getNombre())) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
    }

    private function setDefaultValues(Cliente $cliente): void
    {
        if (!$cliente->getFechaRegistro()) {
            $cliente->setFechaRegistro($this->commonService->getCurrentDateTime());
        }

        if (!$cliente->getCompraTotales()) {
            $cliente->setCompraTotales('0.00');
        }
    }
}
