<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class TaskListProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $name,
        public DateTime $startDate,
        public DateTime $finishDate,
        public string $ownerId,
        public string $ownerFullName,
        public int $status,
        public string $projectId,
        public int $linksCount = 0
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }
}
