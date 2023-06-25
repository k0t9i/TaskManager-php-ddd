<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain;

use TaskManager\Shared\Domain\Exception\DomainException;

final class ProjectionDoesNotExistException extends DomainException
{
    /**
     * @param class-string $projectionName
     */
    public function __construct(string $id, string $projectionName)
    {
        $message = sprintf(
            'Projection "%s" "%s" doesn\'t exist',
            $id,
            $projectionName
        );
        parent::__construct($message, self::CODE_NOT_FOUND);
    }
}
