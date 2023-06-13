<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;

final class TaskStartDateType extends DateTimeType
{
    private const TYPE_NAME = 'task_finish_date';

    public function convertToPHPValue($value, AbstractPlatform $platform): TaskStartDate
    {
        return new TaskStartDate($value);
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
