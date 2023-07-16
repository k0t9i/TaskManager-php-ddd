<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Exception;

final class CriteriaFilterNotExistException extends DomainException
{
    public function __construct(string $field)
    {
        $message = sprintf(
            'Filter field "%s" doesn\'t exist',
            $field
        );
        parent::__construct($message, self::CODE_FORBIDDEN);
    }
}
