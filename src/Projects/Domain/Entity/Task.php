<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\ValueObject\ActiveTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class Task extends AggregateRoot
{
    private bool $isDraft = false;

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
        $task->markAsDraft();

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

    public function changeInformation(
        ?TaskName $name,
        ?TaskBrief $brief,
        ?TaskDescription $description,
        ?TaskStartDate $startDate,
        ?TaskFinishDate $finishDate,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();

        $information = new TaskInformation(
            $name ?? $this->information->name,
            $brief ?? $this->information->brief,
            $description ?? $this->information->description,
            $startDate ?? $this->information->startDate,
            $finishDate ?? $this->information->finishDate,
        );

        $information->ensureFinishDateGreaterOrEqualStartDate();

        if (!$this->information->equals($information)) {
            $this->information = $information;

            $this->registerEvent(new TaskInformationWasChangedEvent(
                $this->id->value,
                $information->name->value,
                $information->brief->value,
                $information->description->value,
                $information->startDate->getValue(),
                $information->finishDate->getValue()
            ));
        }

        // this check must be at the end of the method
        $this->owner->ensureUserIsOwner($currentUserId);
    }

    public function activate(ProjectUserId $currentUserId): void
    {
        $this->changeStatus(new ActiveTaskStatus(), $currentUserId);
    }

    public function close(ProjectUserId $currentUserId): void
    {
        $this->changeStatus(new ClosedTaskStatus(), $currentUserId);
    }

    public function undraft(): void
    {
        $this->isDraft = false;
    }

    public function limitDates(DateTime $date): void
    {
        $this->information = $this->information->limitDates($date);
    }

    public function getId(): TaskId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    private function markAsDraft(): void
    {
        $this->isDraft = true;
    }

    private function changeStatus(TaskStatus $status, ProjectUserId $currentUserId): void
    {
        $this->status->ensureCanBeChangedTo($status);

        $this->status = $status;

        $this->registerEvent(new TaskStatusWasChangedEvent(
            $this->id->value,
            (string) $status->getScalar()
        ));

        // this check must be at the end of the method
        $this->owner->ensureUserIsOwner($currentUserId);
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->projectId->equals($this->projectId)
            && $other->information->equals($this->information)
            && $other->status->equals($this->status)
            && $other->owner->equals($this->owner)
            && $other->isDraft === $this->isDraft;
    }
}
