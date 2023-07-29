<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class UserRequestMemento
{
    public function __construct(
        public string $id,
        public int $status,
        public string $changeDate,
        public string $projectId,
        public string $projectName
    ) {
    }
}
