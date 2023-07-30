<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Exception;

use DomainException as CoreDomainException;

class DomainException extends CoreDomainException
{
    public const CODE_UNAUTHORIZED = 401;
    public const CODE_FORBIDDEN = 403;
    public const CODE_NOT_FOUND = 404;
    public const CODE_CONFLICT = 409;
    public const CODE_UNPROCESSABLE_ENTITY = 422;
}
