<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projects\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\RequestDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserAlreadyHasPendingRequestException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ConfirmedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectUser;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;

final class Project extends AggregateRoot
{
    public function __construct(
        private readonly ProjectId $id,
        private ProjectInformation $information,
        private ProjectStatus $status,
        private ProjectOwner $owner,
        private Collection $participants,
        private Collection $requests,
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
            new ArrayCollection(),
            new ArrayCollection()
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
    }

    public function changeOwner(ProjectOwner $owner, ProjectUserId $currentUserId): void
    {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsOwner($currentUserId);

        $this->owner->ensureUserIsNotOwner($owner->id);
        $this->ensureUserIsNotParticipant($owner->id);
        //TODO add checks for task existence

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

    public function removeParticipant(ProjectUser $participant, ProjectUserId $currentUserId): void
    {
        $this->owner->ensureUserIsOwner($currentUserId);
        $this->removeParticipantInner($participant->id);
    }

    public function leaveProject(ProjectUser $participant): void
    {
        $this->removeParticipantInner($participant->id);
    }

    public function createRequest(RequestId $id, ProjectUserId $userId): Request
    {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsNotOwner($userId);
        $this->ensureUserIsNotParticipant($userId);
        $this->ensureUserDoesNotHavePendingRequest($userId);

        $request = Request::create($id, $userId);

        $this->requests->add($request);

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
        //TODO add checks for task existence

        $this->ensureUserIsParticipant($participantId);
        $this->participants->removeElement($this->getParticipant($participantId));

        $this->registerEvent(new ProjectParticipantWasRemovedEvent(
            $this->id->value,
            $participantId->value
        ));
    }

    private function ensureUserIsParticipant(ProjectUserId $userId): void
    {
        if (null === $this->getParticipant($userId)) {
            throw new ProjectParticipantDoesNotExistException($userId->value);
        }
    }

    private function ensureUserIsNotParticipant(ProjectUserId $userId): void
    {
        if (null !== $this->getParticipant($userId)) {
            throw new UserIsAlreadyProjectParticipantException($userId->value);
        }
    }

    private function getParticipant(ProjectUserId $userId): ?ProjectUser
    {
        return $this->participants->findFirst(function ($key, ProjectUser $participant) use ($userId) {
            return $participant->id->equals($userId);
        });
    }

    private function changeRequestStatus(
        RequestId $id,
        RequestStatus $status,
        ProjectUserId $currentUserId
    ): void {
        $this->status->ensureAllowsModification();
        $this->owner->ensureUserIsOwner($currentUserId);
        $request = $this->getRequest($id);
        if (null === $request) {
            throw new RequestDoesNotExistException($id->value, $this->id->value);
        }

        $request->changeStatus($status);

        if ($status->isConfirmed()) {
            $this->participants->add(new ProjectUser($request->getUserId()));
        }

        $this->registerEvent(new RequestStatusWasChangedEvent(
            $this->id->value,
            $request->getId()->value,
            $request->getUserId()->value,
            (string) $request->getStatus()->getScalar(),
            $request->getChangeDate()->getValue()
        ));
    }

    private function ensureUserDoesNotHavePendingRequest(ProjectUserId $userId): void
    {
        $request = $this->requests->findFirst(function ($key, Request $request) use ($userId) {
            return $request->isPendingForUser($userId);
        });

        if (null !== $request) {
            throw new UserAlreadyHasPendingRequestException($userId->value, $this->id->value);
        }
    }

    private function getRequest(RequestId $requestId): ?Request
    {
        return $this->requests->findFirst(function ($key, Request $request) use ($requestId) {
            return $request->getId()->equals($requestId);
        });
    }
}
