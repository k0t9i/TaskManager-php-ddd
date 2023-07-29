<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class ProjectMemento
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $finishDate,
        public int $status,
        public bool $isOwner
    ) {
    }
}
