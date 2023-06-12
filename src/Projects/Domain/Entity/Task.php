<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;

final class Task extends AggregateRoot
{
    public function __construct(
        private readonly TaskId $id,
        private TaskInformation $information,
        private TaskOwner $owner,
        private TaskStatus $status
    ) {
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->information->equals($this->information)
            && $other->status->equals($this->status)
            && $other->owner->equals($this->owner);
    }
}
