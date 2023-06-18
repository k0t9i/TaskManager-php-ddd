<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Service\DTO;

final readonly class TaskInformationDTO
{
    public function __construct(
        public string $name = '',
        public string $brief = '',
        public string $description = '',
        public string $startDate = '',
        public string $finishDate = ''
    ) {
    }
}
