<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use DateTimeImmutable;
use Exception;
use Stringable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

class DateTime implements Stringable
{
    // ATOM with microseconds
    public const DEFAULT_FORMAT = 'Y-m-d\TH:i:s.uP';

    private DateTimeImmutable $dateTime;

    /**
     * @param string|null $value
     */
    public function __construct(string $value = null)
    {
        try {
            if ($value) {
                $this->dateTime = new DateTimeImmutable($value);
            } else {
                $this->dateTime = DateTimeImmutable::createFromFormat(
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
}
