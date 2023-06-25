<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;

final class TaskLinkProjection implements Hashable
{
    public function __construct(
        public string $taskId,
        public string $linkedTaskId,
        public string $linkedTaskName,
        public string $userId
    ) {
    }

    public function getHash(): string
    {
        return $this->userId;
    }
}
