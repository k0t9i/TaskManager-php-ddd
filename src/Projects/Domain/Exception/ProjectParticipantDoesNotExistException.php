<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectParticipantDoesNotExistException extends DomainException
{
    public function __construct(string $id)
    {
        $message = sprintf(
            'Project participant "%s" doesn\'t exist',
            $id
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
