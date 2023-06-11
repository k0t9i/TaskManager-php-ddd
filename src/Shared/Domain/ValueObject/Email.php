<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

class Email extends StringValueObject
{
    protected function ensureValidEmail(string $attributeName): void
    {
        if (!empty($this->value) && !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid email address.', $attributeName));
        }
    }

    protected function ensureIsValid(): void
    {
        $this->ensureValidEmail('Email');
    }
}
