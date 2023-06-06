<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use LogicException;
use TaskManager\Projects\Domain\Exception\InvalidProjectStatusTransitionException;
use TaskManager\Projects\Domain\Exception\ProjectModificationIsNotAllowedException;
use TaskManager\Shared\Domain\Equatable;

abstract class ProjectStatus implements Equatable
{
    public const STATUS_CLOSED = 0;
    public const STATUS_ACTIVE = 1;

    public function getScalar(): int
    {
        if ($this instanceof ClosedProjectStatus) {
            return self::STATUS_CLOSED;
        }
        if ($this instanceof ActiveProjectStatus) {
            return self::STATUS_ACTIVE;
        }

        throw new LogicException(sprintf('Invalid type "%s" of project status', gettype($this)));
    }

    public static function createFromScalar(int $status): static
    {
        if (self::STATUS_CLOSED === $status) {
            return new ClosedProjectStatus();
        }
        if (self::STATUS_ACTIVE === $status) {
            return new ActiveProjectStatus();
        }

        throw new LogicException(sprintf('Invalid project status "%s"', gettype($status)));
    }

    public function isClosed(): bool
    {
        return self::STATUS_CLOSED === $this->getScalar();
    }

    public function canBeChangedTo(self $status): bool
    {
        return in_array(get_class($status), $this->getNextStatuses(), true);
    }

    public function ensureCanBeChangedTo(self $status): void
    {
        if (!$this->canBeChangedTo($status)) {
            throw new InvalidProjectStatusTransitionException(get_class($this), get_class($status));
        }
    }

    public function ensureAllowsModification(): void
    {
        if (!$this->allowsModification()) {
            throw new ProjectModificationIsNotAllowedException(get_class($this));
        }
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof static;
    }

    abstract protected function getNextStatuses(): array;

    abstract public function allowsModification(): bool;
}
