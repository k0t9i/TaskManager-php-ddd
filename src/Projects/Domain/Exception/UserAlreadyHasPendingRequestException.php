<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class UserAlreadyHasPendingRequestException extends DomainException
{
    public function __construct(string $id, string $projectId)
    {
        $message = sprintf(
            'User "%s" already has request to project "%s"',
            $id,
            $projectId
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
