<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Projects\Domain\Exception\TasksOfTaskLinkAreEqualException;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Hashable;

final readonly class TaskLink implements Equatable, Hashable
{
    public function __construct(
        public TaskId $taskId,
        public TaskId $linkedTaskId
    ) {
        $this->ensureTasksAreNotEqual();
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->taskId->equals($this->taskId)
            && $other->linkedTaskId->equals($this->linkedTaskId);
    }

    public function getHash(): string
    {
        return $this->linkedTaskId->value;
    }

    private function ensureTasksAreNotEqual(): void
    {
        if ($this->taskId->equals($this->linkedTaskId)) {
            throw new TasksOfTaskLinkAreEqualException($this->taskId->value);
        }
    }
}
