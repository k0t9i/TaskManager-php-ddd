<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class TaskLinkMemento
{
    public function __construct(
        public string $taskId,
        public string $linkedTaskId,
        public string $linkedTaskName,
        public int $linkedTaskStatus
    ) {
    }
}
