<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Exception;

use TaskManager\Shared\Domain\Exception\DomainException;

final class EmailIsAlreadyTakenException extends DomainException
{
    public function __construct(string $email)
    {
        $message = sprintf(
            'Email "%s" is already taken',
            $email
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
