<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class TasksOfTaskLinkAreEqualException extends DomainException
{
    public function __construct(string $id)
    {
        $message = sprintf(
            'Link task "%s" to itself is forbidden',
            $id
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
