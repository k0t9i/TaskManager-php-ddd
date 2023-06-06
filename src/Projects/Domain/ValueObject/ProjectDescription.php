<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\ValueObject\StringValueObject as StringValueObjectAlias;

final class ProjectDescription extends StringValueObjectAlias
{
    private const MAX_LENGTH = 4000;

    protected function ensureIsValid(): void
    {
        $this->ensureValidMaxLength('Project description', self::MAX_LENGTH);
    }
}
