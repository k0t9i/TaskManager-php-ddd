<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\ValueObject;

use TaskManager\Shared\Domain\ValueObject\Email;

final class UserEmail extends Email
{
    protected function ensureIsValid(): void
    {
        $attributeName = 'User email';
        $this->ensureNotEmpty($attributeName);
        $this->ensureValidEmail($attributeName);
    }
}
