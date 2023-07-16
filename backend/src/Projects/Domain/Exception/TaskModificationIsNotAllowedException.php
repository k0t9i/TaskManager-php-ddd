<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class TaskModificationIsNotAllowedException extends DomainException
{
    public function __construct(string $status)
    {
        $message = sprintf(
            'Task modification is not allowed when status is "%s"',
            $status
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
