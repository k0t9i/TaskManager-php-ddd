<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\Collection\TaskLinkCollection;
use TaskManager\Projects\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projects\Domain\Event\TaskLinkWasDeleted;
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
use TaskManager\Projects\Domain\ValueObject\TaskLink;
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

    private function __construct(
        private readonly TaskId $id,
        private readonly ProjectId $projectId,
        private TaskInformation $information,
        private TaskStatus $status,
        private TaskOwner $owner,
        private TaskLinkCollection $links
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
        $task = new self(
            $id,
            $projectId,
            $information,
            $status,
            $owner,
            new TaskLinkCollection()
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
            $owner->id->value,
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
                $information->finishDate->getValue(),
                $currentUserId->value
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

    public function createLink(
        TaskId $linkedTaskId,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();

        $link = new TaskLink($this->id, $linkedTaskId);
        $this->links->ensureTaskLinkDoesNotExist($link);

        $this->links->addOrUpdateElement($link);

        $this->registerEvent(new TaskLinkWasCreated(
            $this->id->value,
            $linkedTaskId->value,
            $currentUserId->value
        ));

        // this check must be at the end of the method
        $this->owner->ensureUserIsOwner($currentUserId);
    }

    public function deleteLink(
        TaskId $linkedTaskId,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();

        $link = new TaskLink($this->id, $linkedTaskId);
        $this->links->ensureTaskLinkExists($link);

        $this->links->remove($link->getHash());

        $this->registerEvent(new TaskLinkWasDeleted(
            $this->id->value,
            $linkedTaskId->value,
            $currentUserId->value
        ));

        // this check must be at the end of the method
        $this->owner->ensureUserIsOwner($currentUserId);
    }

    public function closeAsNeeded(ProjectUserId $performerId): void
    {
        $this->changeStatus(new ClosedTaskStatus(), $performerId);
    }

    public function undraft(): void
    {
        $this->isDraft = false;
    }

    public function limitDates(DateTime $date, ProjectUserId $performerId): void
    {
        $information = $this->information->limitDates($date);

        if (!$information->equals($this->information)) {
            $this->information = $information;
            $this->registerEvent(new TaskInformationWasChangedEvent(
                $this->id->value,
                $information->name->value,
                $information->brief->value,
                $information->description->value,
                $information->startDate->getValue(),
                $information->finishDate->getValue(),
                $performerId->value
            ));
        }
    }

    public function createBackLink(TaskId $linkedTaskId, ProjectUserId $performerId): void
    {
        $link = new TaskLink($this->id, $linkedTaskId);

        if (!$this->links->exists($link->getHash())) {
            $this->links->addOrUpdateElement($link);
            $this->registerEvent(new TaskLinkWasCreated(
                $this->id->value,
                $linkedTaskId->value,
                $performerId->value
            ));
        }
    }

    public function deleteBackLink(TaskId $linkedTaskId, ProjectUserId $performerId): void
    {
        $link = new TaskLink($this->id, $linkedTaskId);

        if ($this->links->exists($link->getHash())) {
            $this->links->remove($link->getHash());
            $this->registerEvent(new TaskLinkWasDeleted(
                $this->id->value,
                $linkedTaskId->value,
                $performerId->value
            ));
        }
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
            (string) $status->getScalar(),
            $currentUserId->value
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
