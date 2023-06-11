<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class RequestDoesNotExistException extends DomainException
{
    public function __construct(string $id, string $projectId)
    {
        $message = sprintf(
            'Request "%s" to project "%" doesn\'t exist',
            $id,
            $projectId
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
