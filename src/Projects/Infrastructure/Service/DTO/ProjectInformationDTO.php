<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Service\DTO;

final readonly class ProjectInformationDTO
{
    public function __construct(
        public string $name = '',
        public string $description = '',
        public string $finishDate = ''
    ) {
    }
}
