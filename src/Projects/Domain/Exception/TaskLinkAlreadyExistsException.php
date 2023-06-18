<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class TaskLinkAlreadyExistsException extends DomainException
{
    public function __construct(string $fromTaskId, string $toTaskId)
    {
        $message = sprintf(
            'Link from task "%s" to task "%s" already exists',
            $fromTaskId,
            $toTaskId
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
