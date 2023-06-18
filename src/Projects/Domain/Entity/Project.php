<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\Collection\ParticipantCollection;
use TaskManager\Projects\Domain\Collection\ProjectTaskCollection;
use TaskManager\Projects\Domain\Collection\RequestCollection;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projects\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskFinishDateWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskWasClosedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskWasCreatedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\ProjectUserDoesNotExistException;
use TaskManager\Projects\Domain\Exception\RequestDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserIsNotTaskOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ConfirmedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectTask;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;

final class Project extends AggregateRoot
{
    private function __construct(
        private readonly ProjectId $id,
        private ProjectInformation $information,
        private ProjectStatus $status,
        private ProjectOwner $owner,
        private ParticipantCollection $participants,
        private RequestCollection $requests,
        private ProjectTaskCollection $tasks
    ) {
    }

    public static function create(
        ProjectId $id,
        ProjectInformation $information,
        ProjectOwner $owner
    ): self {
        $status = new ActiveProjectStatus();
        $project = new self(
            $id,
            $information,
            $status,
            $owner,
            new ParticipantCollection(),
            new RequestCollection(),
            new ProjectTaskCollection()
        );

        $project->registerEvent(new ProjectWasCreatedEvent(
            $id->value,
            $information->name->value,
            $information->description->value,
            $information->finishDate->getValue(),
            (string) $status->getScalar(),
            $owner->id->value
        ));

        return $project;
    }

    public function changeInformation(
        ?ProjectName $name,
        ?ProjectDescription $description,
        ?ProjectFinishDate $finishDate,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsOwner($currentUserId);

        $information = new ProjectInformation(
            $name ?? $this->information->name,
            $description ?? $this->information->description,
            $finishDate ?? $this->information->finishDate,
        );

        if (!$this->information->equals($information)) {
            if (!$information->finishDate->equals($this->information->finishDate)) {
                /** @var ProjectTask $task */
                foreach ($this->tasks->getItems() as $task) {
                    $this->registerEvent(new ProjectTaskFinishDateWasChangedEvent(
                        $this->id->value,
                        $task->taskId->value,
                        $information->finishDate->getValue()
                    ));
                }
            }
            $this->information = $information;

            $this->registerEvent(new ProjectInformationWasChangedEvent(
                $this->id->value,
                $information->name->value,
                $information->description->value,
                $information->finishDate->getValue()
            ));
        }
    }

    public function activate(ProjectUserId $currentUserId): void
    {
        $this->changeStatus(new ActiveProjectStatus(), $currentUserId);
    }

    public function close(ProjectUserId $currentUserId): void
    {
        $this->changeStatus(new ClosedProjectStatus(), $currentUserId);
        /** @var ProjectTask $task */
        foreach ($this->tasks->getItems() as $task) {
            $this->registerEvent(new ProjectTaskWasClosedEvent(
                $this->id->value,
                $task->taskId->value,
            ));
        }
    }

    public function changeOwner(ProjectOwner $owner, ProjectUserId $currentUserId): void
    {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsOwner($currentUserId);

        $this->owner->ensureUserIsNotOwner($owner->id);
        $this->participants->ensureUserIsNotParticipant($owner->id);
        $this->tasks->ensureUserDoesNotHaveTask($this->owner->id, $this->id);

        $this->owner = $owner;

        $this->registerEvent(new ProjectOwnerWasChangedEvent(
            $this->id->value,
            $this->owner->id->value
        ));
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->information->equals($this->information)
            && $other->status->equals($this->status)
            && $other->owner->equals($this->owner);
    }

    public function removeParticipant(ProjectUserId $participantId, ProjectUserId $currentUserId): void
    {
        $this->owner->ensureUserIsOwner($currentUserId);
        $this->removeParticipantInner($participantId);
    }

    public function leaveProject(ProjectUserId $participantId): void
    {
        $this->removeParticipantInner($participantId);
    }

    public function createRequest(RequestId $id, ProjectUserId $userId): Request
    {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsNotOwner($userId);
        $this->participants->ensureUserIsNotParticipant($userId);
        $this->requests->ensureUserDoesNotHavePendingRequest($userId, $this->id);

        $request = Request::create($id, $this->id, $userId);

        $this->requests->addOrUpdateElement($request);

        $this->registerEvent(new RequestWasCreatedEvent(
            $this->id->value,
            $id->value,
            $userId->value,
            (string) $request->getStatus()->getScalar(),
            $request->getChangeDate()->getValue()
        ));

        return $request;
    }

    public function confirmRequest(RequestId $id, ProjectUserId $currentUserId): void
    {
        $this->changeRequestStatus($id, new ConfirmedRequestStatus(), $currentUserId);
    }

    public function rejectRequest(RequestId $id, ProjectUserId $currentUserId): void
    {
        $this->changeRequestStatus($id, new RejectedRequestStatus(), $currentUserId);
    }

    public function createTask(
        TaskId $id,
        TaskInformation $information,
        TaskOwner $owner
    ): Task {
        $this->status->ensureAllowsModification();
        $this->ensureUserIsProjectUser($owner->id);
        $this->information->ensureIsFinishDateGreaterThanTaskDates(
            $information->startDate,
            $information->finishDate
        );

        return Task::create($id, $this->id, $information, $owner);
    }

    public function changeTaskInformation(
        Task $task,
        ?TaskName $name,
        ?TaskBrief $brief,
        ?TaskDescription $description,
        ?TaskStartDate $startDate,
        ?TaskFinishDate $finishDate,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();
        $this->tasks->ensureProjectTaskExists($task->getId());
        $this->information->ensureIsFinishDateGreaterThanTaskDates(
            $startDate,
            $finishDate
        );

        try {
            $task->changeInformation(
                $name,
                $brief,
                $description,
                $startDate,
                $finishDate,
                $currentUserId
            );
        } catch (UserIsNotTaskOwnerException $e) {
            if (!$this->owner->userIsOwner($currentUserId)) {
                throw $e;
            }
        }
    }

    public function activateTask(Task $task, ProjectUserId $currentUserId): void
    {
        $this->status->ensureAllowsModification();
        $this->tasks->ensureProjectTaskExists($task->getId());

        try {
            $task->activate($currentUserId);
        } catch (UserIsNotTaskOwnerException $e) {
            if (!$this->owner->userIsOwner($currentUserId)) {
                throw $e;
            }
        }
    }

    public function closeTask(Task $task, ProjectUserId $currentUserId): void
    {
        $this->status->ensureAllowsModification();
        $this->tasks->ensureProjectTaskExists($task->getId());

        try {
            $task->close($currentUserId);
        } catch (UserIsNotTaskOwnerException $e) {
            if (!$this->owner->userIsOwner($currentUserId)) {
                throw $e;
            }
        }
    }

    public function addProjectTask(TaskId $taskId, ProjectUserId $userId): void
    {
        $this->status->ensureAllowsModification();
        $this->ensureUserIsProjectUser($userId);

        $this->tasks->addOrUpdateElement(new ProjectTask(
            $this->id,
            $taskId,
            $userId
        ));

        $this->registerEvent(new ProjectTaskWasCreatedEvent(
            $this->id->value,
            $taskId->value,
            $userId->value
        ));
    }

    public function getId(): ProjectId
    {
        return $this->id;
    }

    private function changeStatus(ProjectStatus $status, ProjectUserId $currentUserId): void
    {
        $this->status->ensureCanBeChangedTo($status);
        $this->owner->ensureUserIsOwner($currentUserId);

        $this->status = $status;

        $this->registerEvent(new ProjectStatusWasChangedEvent(
            $this->id->value,
            (string) $status->getScalar()
        ));
    }

    private function removeParticipantInner(ProjectUserId $participantId): void
    {
        $this->status->ensureAllowsModification();
        $this->participants->ensureUserIsParticipant($participantId);
        $this->tasks->ensureUserDoesNotHaveTask($participantId, $this->id);

        $this->participants->remove($participantId->value);

        $this->registerEvent(new ProjectParticipantWasRemovedEvent(
            $this->id->value,
            $participantId->value
        ));
    }

    private function changeRequestStatus(
        RequestId $id,
        RequestStatus $status,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsOwner($currentUserId);
        if (!$this->requests->exists($id->value)) {
            throw new RequestDoesNotExistException($id->value, $this->id->value);
        }

        /** @var Request $request */
        $request = $this->requests->get($id->value);
        $request->changeStatus($status);

        if ($status->isConfirmed()) {
            $this->participants->addOrUpdateElement(new Participant(
                $this->id,
                $request->getUserId()
            ));
        }

        $this->registerEvent(new RequestStatusWasChangedEvent(
            $this->id->value,
            $request->getId()->value,
            $request->getUserId()->value,
            (string) $request->getStatus()->getScalar(),
            $request->getChangeDate()->getValue()
        ));
    }

    private function ensureUserIsProjectUser(ProjectUserId $userId): void
    {
        if (!$this->owner->userIsOwner($userId) && !$this->participants->exists($userId->value)) {
            throw new ProjectUserDoesNotExistException($userId->value);
        }
    }
}
