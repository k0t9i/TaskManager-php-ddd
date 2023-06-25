<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;

final class TaskListProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public \DateTime $startDate,
        public \DateTime $finishDate,
        public string $ownerId,
        public string $ownerEmail,
        public string $ownerFirstname,
        public string $ownerLastname,
        public int $status,
        public string $projectId,
        public int $linksCount = 0
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
