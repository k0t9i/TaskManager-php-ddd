<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use Stringable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

abstract class StringValueObject implements Stringable
{
    final public function __construct(public readonly string $value)
    {
        $this->ensureIsValid();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    abstract protected function ensureIsValid(): void;

    protected function ensureValidMaxLength(string $attributeName, int $maxLength): void
    {
        if (mb_strlen($this->value) > $maxLength) {
            throw new InvalidArgumentException(
                sprintf('"%s" should contain at most %s characters.', $attributeName, $maxLength)
            );
        }
    }

    protected function ensureValidMinLength(string $attributeName, int $minLength): void
    {
        if (mb_strlen($this->value) < $minLength) {
            throw new InvalidArgumentException(
                sprintf('"%s" should contain at least %s characters.', $attributeName, $minLength)
            );
        }
    }

    protected function ensureNotEmpty(string $attributeName): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException(sprintf('"%s" cannot be blank.', $attributeName));
        }
    }
}