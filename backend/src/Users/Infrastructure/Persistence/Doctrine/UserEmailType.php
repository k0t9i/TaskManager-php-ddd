<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Users\Domain\ValueObject\UserEmail;

final class UserEmailType extends StringType
{
    private const TYPE_NAME = 'user_email';

    public function convertToPHPValue($value, AbstractPlatform $platform): UserEmail
    {
        return new UserEmail($value);
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
