<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class ProjectRequestMemento
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $userFullName,
        public int $status,
        public string $changeDate
    ) {
    }
}
