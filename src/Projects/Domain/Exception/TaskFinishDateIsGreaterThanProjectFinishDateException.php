<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class TaskFinishDateIsGreaterThanProjectFinishDateException extends DomainException
{
    public function __construct(string $projectFinishDate, string $finishDate)
    {
        $message = sprintf(
            'Task finish date "%s" is greater than project finish date "%s"',
            $finishDate,
            $projectFinishDate
        );
        parent::__construct($message, self::CODE_UNPROCESSABLE_ENTITY);
    }
}
