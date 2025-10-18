<?php

namespace App\Service;

class CommonService
{
    public function getCurrentDateTime(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('America/Toronto'));
    }

    public function getCurrentUser(): string
    {
        $user = $this->security->getUser();
        return $user ? $user->getUsername() : 'Sistema';
    }
}
