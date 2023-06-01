<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use Stringable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

abstract class Uuid implements Stringable
{
    public function __construct(public readonly string $value)
    {
        $this->ensureIsValidUuid($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function createFrom(Uuid $other): static
    {
        return new static($other->value);
    }

    private function ensureIsValidUuid(string $value): void
    {
        $pattern = '/\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}\z/Dms';
        if (!preg_match($pattern, $value)) {
            throw new InvalidArgumentException(sprintf('Invalid uuid "%s"', $value));
        }
    }
}