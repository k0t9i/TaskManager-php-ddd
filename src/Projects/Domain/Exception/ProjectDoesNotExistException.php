<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectDoesNotExistException extends DomainException
{
    public function __construct(string $projectId)
    {
        $message = sprintf(
            'Project "%s" doesn\'t exist',
            $projectId
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
