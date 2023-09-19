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
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskLink;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\ValueObject\DateTime;
use TaskManager\Shared\Domain\ValueObject\UserId;

final class Task extends AggregateRoot
{
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
        TaskInformation $information,
        UserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();

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

    public function activate(UserId $currentUserId): void
    {
        $this->changeStatus(new ActiveTaskStatus(), $currentUserId);
    }

    public function close(UserId $currentUserId): void
    {
        $this->changeStatus(new ClosedTaskStatus(), $currentUserId);
    }

    public function createLink(
        TaskId $linkedTaskId,
        UserId $currentUserId
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
        UserId $currentUserId
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

    public function closeAsNeeded(UserId $performerId): void
    {
        if (!$this->status->isClosed()) {
            $status = new ClosedTaskStatus();
            $this->status = $status;

            $this->registerEvent(new TaskStatusWasChangedEvent(
                $this->id->value,
                (string) $status->getScalar(),
                $performerId->value
            ));
        }
    }

    public function limitDates(DateTime $date, UserId $performerId): void
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

    public function createBackLink(TaskId $linkedTaskId, UserId $performerId): void
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

    public function deleteBackLink(TaskId $linkedTaskId, UserId $performerId): void
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

    private function changeStatus(TaskStatus $status, UserId $currentUserId): void
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
            && $other->owner->equals($this->owner);
    }
}
