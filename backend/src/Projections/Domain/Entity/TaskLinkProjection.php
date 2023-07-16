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
        public int $linkedTaskStatus
    ) {
    }

    public static function hash(string $taskId, string $linkedTaskId): string
    {
        return $taskId.$linkedTaskId;
    }

    public function getHash(): string
    {
        return self::hash($this->taskId, $this->linkedTaskId);
    }
}
