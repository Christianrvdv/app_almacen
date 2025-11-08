<?php

namespace App\Service\Cliente;

use App\Entity\Cliente;
use App\Service\Cliente\Interface\ClienteServiceInterface;
use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;

class ClienteService implements ClienteServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommonService $commonService
    ) {}

    public function create(Cliente $cliente): void
    {
        $this->validate($cliente);
        $this->setDefaultValues($cliente);

        $this->entityManager->persist($cliente);
        $this->entityManager->flush();
    }

    public function update(Cliente $cliente): void
    {
        $this->validate($cliente);
        $this->entityManager->flush();
    }

    public function delete(Cliente $cliente): void
    {
        $this->entityManager->remove($cliente);
        $this->entityManager->flush();
    }

    private function validate(Cliente $cliente): void
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
