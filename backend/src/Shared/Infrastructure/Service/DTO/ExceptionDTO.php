<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service\DTO;

final readonly class ExceptionDTO
{
    public function __construct(
        public string $message,
        public int $httpCode,
        public ?string $file = null,
        public ?int $line = null,
        public ?array $trace = null
    ) {
    }
}
