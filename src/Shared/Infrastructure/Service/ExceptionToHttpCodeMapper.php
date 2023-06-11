<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Response;
use TaskManager\Shared\Domain\Exception\DomainException;

final class ExceptionToHttpCodeMapper implements ExceptionToHttpCodeMapperInterface
{
    private const CODE_DEFAULT = Response::HTTP_INTERNAL_SERVER_ERROR;

    private array $map = [
        DomainException::CODE_UNAUTHORIZED => Response::HTTP_UNAUTHORIZED,
        DomainException::CODE_FORBIDDEN => Response::HTTP_FORBIDDEN,
        DomainException::CODE_NOT_FOUND => Response::HTTP_NOT_FOUND,
        DomainException::CODE_UNPROCESSABLE_ENTITY => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    public function getHttpCode(\Throwable $exception): int
    {
        return $this->map[$exception->getCode()] ?? self::CODE_DEFAULT;
    }
}
