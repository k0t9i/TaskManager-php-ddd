<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\ValueObject;

use TaskManager\Shared\Domain\ValueObject\StringValueObject;

final class UserPassword extends StringValueObject
{
    protected function ensureIsValid(): void
    {
        $this->ensureNotEmpty('Password');
    }
}
