<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;

final class TaskProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public string $brief,
        public string $description,
        public \DateTime $startDate,
        public \DateTime $finishDate,
        public string $ownerId,
        public int $status,
        public string $projectId
    ) {
    }

    public function getHash(): string
    {
        return $this->userId;
    }

    public function __clone()
    {
        $this->finishDate = clone $this->finishDate;
    }
}