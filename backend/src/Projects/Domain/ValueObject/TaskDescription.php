<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\ValueObject\StringValueObject;

final class TaskDescription extends StringValueObject
{
    private const MAX_LENGTH = 4000;

    protected function ensureIsValid(): void
    {
        $this->ensureValidMaxLength('Task description', self::MAX_LENGTH);
    }
}
