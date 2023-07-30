<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Exception;

final class OptimisticLockException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Entity data is out of date', self::CODE_CONFLICT);
    }
}
