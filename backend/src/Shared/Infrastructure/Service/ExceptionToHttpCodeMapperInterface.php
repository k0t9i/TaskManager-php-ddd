<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

interface ExceptionToHttpCodeMapperInterface
{
    public function getHttpCode(\Throwable $exception): int;
}
