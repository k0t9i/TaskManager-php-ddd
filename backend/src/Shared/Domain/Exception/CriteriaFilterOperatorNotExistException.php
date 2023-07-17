<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Exception;

final class CriteriaFilterOperatorNotExistException extends DomainException
{
    public function __construct(string $operator, string $field)
    {
        $message = sprintf(
            'Filter operator "%s" for field "%s" doesn\'t exist',
            $operator,
            $field
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
