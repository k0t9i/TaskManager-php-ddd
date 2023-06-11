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

            if ($value) {
                try {
                    $this->dateTime = new \DateTimeImmutable($value);
                } catch (Exception) {
                    throw new InvalidArgumentException(sprintf('Invalid datetime value "%s"', $value));
                }
            } else {
                $dateTime = \DateTimeImmutable::createFromFormat(
                    'U.u',
                    sprintf('%.f', microtime(true))
                );
                if (false === $dateTime) {
                    throw new \LogicException('Cannot create DateTimeImmutable from format');
                }
                $this->dateTime = $dateTime;
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
