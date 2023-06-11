<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;

final class ProjectUserIdType extends StringType
{
    private const TYPE_NAME = 'project_user_id';

    public function convertToPHPValue($value, AbstractPlatform $platform): ProjectUserId
    {
        return new ProjectUserId($value);
    }

    /**
     * @param ProjectUserId $value
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
