<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Projects\Domain\Exception\InvalidTaskStatusTransitionException;
use TaskManager\Projects\Domain\Exception\TaskModificationIsNotAllowedException;

abstract class TaskStatus extends Status
{
    public const STATUS_CLOSED = 0;
    public const STATUS_ACTIVE = 1;

    public function getScalar(): int
    {
        if ($this instanceof ClosedTaskStatus) {
            return self::STATUS_CLOSED;
        }
        if ($this instanceof ActiveTaskStatus) {
            return self::STATUS_ACTIVE;
        }

        throw new \LogicException(sprintf('Invalid type "%s" of task status', gettype($this)));
    }

    public static function createFromScalar(?int $status): static
    {
        if (self::STATUS_CLOSED === $status) {
            return new ClosedTaskStatus();
        }
        if (self::STATUS_ACTIVE === $status) {
            return new ActiveTaskStatus();
        }

        throw new \LogicException(sprintf('Invalid task status "%s"', gettype($status)));
    }

    public function ensureCanBeChangedTo(self $status): void
    {
        if (!$this->canBeChangedTo($status)) {
            throw new InvalidTaskStatusTransitionException(get_class($this), get_class($status));
        }
    }

    public function ensureAllowsModification(): void
    {
        if (!$this->allowsModification()) {
            throw new TaskModificationIsNotAllowedException(get_class($this));
        }
    }
}
