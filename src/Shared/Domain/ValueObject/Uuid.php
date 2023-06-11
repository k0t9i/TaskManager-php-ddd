<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

class Uuid implements \Stringable, Equatable
{
    public function __construct(public readonly string $value)
    {
        $this->ensureIsValidUuid($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof static && $this->value === $other->value;
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
