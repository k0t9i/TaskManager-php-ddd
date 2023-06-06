<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class InvalidProjectStatusTransitionException extends DomainException
{
    public function __construct(string $fromStatus, string $toStatus)
    {
        $message = sprintf(
            'Status "%s" cannot be changed to "%s"',
            $fromStatus,
            $toStatus
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
