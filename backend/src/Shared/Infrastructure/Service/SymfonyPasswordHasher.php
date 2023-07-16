<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\PasswordHasher\PasswordHasherInterface as SymfonyPasswordHasherInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private SymfonyPasswordHasherInterface $hasher)
    {
    }

    public function hashPassword(string $plainPassword): string
    {
        return $this->hasher->hash($plainPassword);
    }

    public function verifyPassword(string $hashedPassword, string $plainPassword): bool
    {
        return $this->hasher->verify($hashedPassword, $plainPassword);
    }
}
