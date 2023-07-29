<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class TaskListMemento
{
    public function __construct(
        public string $id,
        public string $name,
        public string $startDate,
        public string $finishDate,
        public string $ownerId,
        public string $ownerFullName,
        public int $status,
        public int $linksCount
    ) {
    }
}
