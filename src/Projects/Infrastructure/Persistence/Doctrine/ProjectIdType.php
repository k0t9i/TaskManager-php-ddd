<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Projects\Domain\ValueObject\ProjectId;

final class ProjectIdType extends StringType
{
    private const TYPE_NAME = 'project_id';

    public function convertToPHPValue($value, AbstractPlatform $platform): ProjectId
    {
        return new ProjectId($value);
    }

    /**
     * @param ProjectId $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->value;
    }

    public function getName(): string
    {
        return self::TYPE_NAME; // modify to match your constant name
    }
}
