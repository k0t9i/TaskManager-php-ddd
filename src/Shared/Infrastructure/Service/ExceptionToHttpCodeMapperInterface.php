<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Throwable;

interface ExceptionToHttpCodeMapperInterface
{
    public function getHttpCode(Throwable $exception): int;
}
