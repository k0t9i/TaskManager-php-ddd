<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use TaskManager\Users\Domain\ValueObject\UserPassword;

final class UserPasswordType extends StringType
{
    private const TYPE_NAME = 'user_password';

    public function convertToPHPValue($value, AbstractPlatform $platform): UserPassword
    {
        return new UserPassword($value);
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
