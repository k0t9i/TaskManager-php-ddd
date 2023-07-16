<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

interface AuthenticatorServiceInterface
{
    public function getUserId(): string;

    public function getToken(string $id): string;
}
