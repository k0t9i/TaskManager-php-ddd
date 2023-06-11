<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Projects\Domain\ValueObject\ProjectName;

final class ProjectNameType extends StringType
{
    private const TYPE_NAME = 'project_name';

    public function convertToPHPValue($value, AbstractPlatform $platform): ProjectName
    {
        return new ProjectName($value);
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
