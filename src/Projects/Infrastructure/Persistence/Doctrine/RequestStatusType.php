<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;

final class RequestStatusType extends IntegerType
{
    private const TYPE_NAME = 'request_status';

    public function convertToPHPValue($value, AbstractPlatform $platform): RequestStatus
    {
        return RequestStatus::createFromScalar(parent::convertToPHPValue($value, $platform));
    }

    /**
     * @param RequestStatus $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return $value->getScalar();
    }

    public function getName(): string
    {
        return self::TYPE_NAME; // modify to match your constant name
    }
}
