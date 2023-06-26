<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class InsufficientPermissionsException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
