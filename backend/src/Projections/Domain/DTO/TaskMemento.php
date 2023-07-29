<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class TaskMemento
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brief,
        public string $description,
        public string $startDate,
        public string $finishDate,
        public string $ownerId,
        public int $status
    ) {
    }
}
