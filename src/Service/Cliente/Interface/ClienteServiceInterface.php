<?php

namespace App\Service\Cliente\Interface;

use App\Entity\Cliente;

interface ClienteServiceInterface
{
    public function create(Cliente $cliente): void;
    public function update(Cliente $cliente): void;
    public function delete(Cliente $cliente): void;
}
