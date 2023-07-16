<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectUserDoesNotExistException extends DomainException
{
    public function __construct(string $userId)
    {
        $message = sprintf(
            'Project user "%s" doesn\'t exist',
            $userId
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
