<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectUserHasTaskException extends DomainException
{
    public function __construct(string $userId, string $projectId)
    {
        $message = sprintf(
            'User "%s" has task(s) in project "%s',
            $userId,
            $projectId
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
