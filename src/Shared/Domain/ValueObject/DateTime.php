<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use Exception;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

class DateTime implements \Stringable, Equatable
{
    // ATOM with microseconds
    public const DEFAULT_FORMAT = 'Y-m-d\TH:i:s.uP';

    private \DateTimeImmutable $dateTime;

    public function __construct(string $value = null)
    {
        try {
            if ($value) {
                $this->dateTime = new \DateTimeImmutable($value);
            } else {
                $this->dateTime = \DateTimeImmutable::createFromFormat(
                    'U.u',
                    sprintf('%.f', microtime(true))
                );
            }
        } catch (Exception) {
            throw new InvalidArgumentException(sprintf('Invalid datetime value "%s"', $value));
        }
    }

    public function getValue(): string
    {
        return $this->dateTime->format(self::DEFAULT_FORMAT);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->dateTime > $other->dateTime;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof static && $this->getValue() === $other->getValue();
    }

    public function getPhpDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
