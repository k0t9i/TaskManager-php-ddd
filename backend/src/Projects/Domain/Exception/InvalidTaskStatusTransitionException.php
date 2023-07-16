<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class InvalidTaskStatusTransitionException extends DomainException
{
    public function __construct(string $fromStatus, string $toStatus)
    {
        $message = sprintf(
            'Task status "%s" cannot be changed to "%s"',
            $fromStatus,
            $toStatus
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
