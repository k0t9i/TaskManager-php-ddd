<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType as SymfonyDateTimeType;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class DateTimeType extends SymfonyDateTimeType
{
    private const TYPE_NAME = 'tm_datetime';

    public function convertToPHPValue($value, AbstractPlatform $platform): DateTime
    {
        return new DateTime($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return parent::convertToDatabaseValue($value->getPhpDateTime(), $platform);
    }

    public function getName(): string
    {
        return self::TYPE_NAME; // modify to match your constant name
    }
}
