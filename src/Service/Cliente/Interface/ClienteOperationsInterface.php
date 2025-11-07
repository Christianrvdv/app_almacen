<?php

namespace App\Service\Cliente\Interface;

use App\Entity\Cliente;

interface ClienteOperationsInterface
{
    public function createCliente(Cliente $cliente): void;
    public function updateCliente(Cliente $cliente): void;
    public function deleteCliente(Cliente $cliente): void;
}
