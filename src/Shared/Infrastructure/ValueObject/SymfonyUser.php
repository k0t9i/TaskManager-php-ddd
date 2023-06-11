<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\ValueObject;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SymfonyUser implements UserInterface
{
    public function __construct(private string $userIdentifier)
    {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
