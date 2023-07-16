<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class TaskStartDateIsGreaterThanProjectFinishDateException extends DomainException
{
    public function __construct(string $projectFinishDate, string $startDate)
    {
        $message = sprintf(
            'Task start date "%s" is greater than project finish date "%s"',
            $startDate,
            $projectFinishDate
        );
        parent::__construct($message, self::CODE_UNPROCESSABLE_ENTITY);
    }
}
