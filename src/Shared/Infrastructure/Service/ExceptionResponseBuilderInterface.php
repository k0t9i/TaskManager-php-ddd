<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Response;
use TaskManager\Shared\Infrastructure\Service\DTO\ExceptionDTO;

interface ExceptionResponseBuilderInterface
{
    public function build(ExceptionDTO $dto, bool $verbose = false): Response;
}
