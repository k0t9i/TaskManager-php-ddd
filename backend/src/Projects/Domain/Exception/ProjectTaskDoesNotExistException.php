<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectTaskDoesNotExistException extends DomainException
{
    public function __construct(string $taskId)
    {
        $message = sprintf(
            'Project task "%s" doesn\'t exist',
            $taskId
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
