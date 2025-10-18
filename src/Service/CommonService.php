<?php

namespace App\Service;


use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CommonService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getCurrentDateTime(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('America/Toronto'));
    }

    public function getCurrentUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    public function getCurrentUsername(): string
    {
        $user = $this->getCurrentUser();
        return $user ? $user->getUsername() : 'Sistema';
    }

    public function getCurrentUserId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user && method_exists($user, 'getId') ? $user->getId() : null;
    }

    public function isUserLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2, '.', ',') . ' ' . $currency;
    }

    public function formatPercentage(float $percentage): string
    {
        return number_format($percentage, 2) . '%';
    }

    public function getDateRange(string $rangeType = 'current_month'): array
    {
        $startDate = null;
        $endDate = $this->getCurrentDateTime();

        switch ($rangeType) {
            case 'today':
                $startDate = clone $endDate;
                $startDate->setTime(0, 0, 0);
                $endDate->setTime(23, 59, 59);
                break;

            case 'yesterday':
                $startDate = clone $endDate;
                $startDate->modify('-1 day')->setTime(0, 0, 0);
                $endDate = clone $startDate;
                $endDate->setTime(23, 59, 59);
                break;

            case 'current_week':
                $startDate = clone $endDate;
                $startDate->modify('monday this week')->setTime(0, 0, 0);
                break;

            case 'last_week':
                $startDate = clone $endDate;
                $startDate->modify('monday last week')->setTime(0, 0, 0);
                $endDate = clone $startDate;
                $endDate->modify('sunday this week')->setTime(23, 59, 59);
                break;

            case 'current_month':
                $startDate = clone $endDate;
                $startDate->modify('first day of this month')->setTime(0, 0, 0);
                break;

            case 'last_month':
                $startDate = clone $endDate;
                $startDate->modify('first day of last month')->setTime(0, 0, 0);
                $endDate = clone $startDate;
                $endDate->modify('last day of last month')->setTime(23, 59, 59);
                break;

            case 'current_year':
                $startDate = clone $endDate;
                $startDate->modify('first day of january this year')->setTime(0, 0, 0);
                break;

            default:
                $startDate = clone $endDate;
                $startDate->modify('first day of this month')->setTime(0, 0, 0);
                break;
        }

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    public function validateDateRange(\DateTime $startDate, \DateTime $endDate): bool
    {
        return $startDate <= $endDate;
    }

    public function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function sanitizeFilename(string $filename): string
    {
        // Remove any path information
        $filename = basename($filename);

        // Replace spaces and special characters
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);

        // Limit length
        if (strlen($filename) > 100) {
            $filename = substr($filename, 0, 100);
        }

        return $filename;
    }
}
