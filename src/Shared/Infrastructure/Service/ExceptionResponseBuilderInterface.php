<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ExceptionResponseBuilderInterface
{
    public function build(string $message, int $code, array $trace): Response;
}
