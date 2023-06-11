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
use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectUser;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;

final class Project extends AggregateRoot
{
    public function __construct(
        private readonly ProjectId $id,
        private ProjectInformation $information,
        private ProjectStatus $status,
        private ProjectOwner $owner,
        private Collection $participants
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
}
