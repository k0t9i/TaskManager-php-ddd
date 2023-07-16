<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Projects\Domain\ValueObject\TaskId;

final class TaskIdType extends StringType
{
    private const TYPE_NAME = 'task_id';

    public function convertToPHPValue($value, AbstractPlatform $platform): TaskId
    {
        return new TaskId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->value;
    }

    public function getName(): string
    {
        return self::TYPE_NAME; // modify to match your constant name
    }
}
