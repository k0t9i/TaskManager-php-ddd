<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;

final class ProjectStatusType extends IntegerType
{
    private const TYPE_NAME = 'project_status';

    public function convertToPHPValue($value, AbstractPlatform $platform): ProjectStatus
    {
        return ProjectStatus::createFromScalar(parent::convertToPHPValue($value, $platform));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return $value->getScalar();
    }

    public function getName(): string
    {
        return self::TYPE_NAME; // modify to match your constant name
    }
}
