<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class PasswordAndRepeatPasswordDoNotMatchException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Password and repeat password do not match', self::CODE_UNPROCESSABLE_ENTITY);
    }
}
