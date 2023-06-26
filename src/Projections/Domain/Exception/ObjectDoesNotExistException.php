<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ObjectDoesNotExistException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
