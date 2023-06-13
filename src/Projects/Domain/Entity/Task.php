<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\ValueObject\ActiveTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
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
        private readonly ProjectId $projectId,
        private TaskInformation $information,
        private TaskStatus $status,
        private TaskOwner $owner
    ) {
    }

    public static function create(
        TaskId $id,
        ProjectId $projectId,
        TaskInformation $information,
        TaskOwner $owner
    ): self {
        $information->ensureFinishDateGreaterOrEqualStartDate();

        $status = new ActiveTaskStatus();
        $task = new Task(
            $id,
            $projectId,
            $information,
            $status,
            $owner
        );

        $task->registerEvent(new TaskWasCreatedEvent(
            $id->value,
            $projectId->value,
            $information->name->value,
            $information->brief->value,
            $information->description->value,
            $information->startDate->getValue(),
            $information->finishDate->getValue(),
            (string) $status->getScalar(),
            $owner->id->value
        ));

        return $task;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->projectId->equals($this->projectId)
            && $other->information->equals($this->information)
            && $other->status->equals($this->status)
            && $other->owner->equals($this->owner);
    }
}
