<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

use TaskManager\Shared\Domain\ValueObject\UserId;

interface AuthenticatorServiceInterface
{
    public function getUserId(): UserId;

    public function getToken(string $id): string;
}
