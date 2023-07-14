<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasAddedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projections\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projections\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Event\UserWasCreatedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectListProjector extends Projector
{
    public function __construct(
        private readonly ProjectListProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var ProjectListProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var ProjectListProjection $item */
        foreach ($this->unitOfWork->getDeletedProjections() as $item) {
            $this->repository->delete($item);
        }

        $this->unitOfWork->flush();
    }

    /**
     * @throws \Exception
     */
    private function whenProjectCreated(ProjectWasCreatedEvent $event): void
    {
        $ownerProjection = $this->userRepository->findById($event->ownerId);
        if (null === $ownerProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        $userProjections = $this->userRepository->findAll();

        foreach ($userProjections as $userProjection) {
            $this->unitOfWork->loadProjection(new ProjectListProjection(
                $event->getAggregateId(),
                $userProjection->id,
                $event->name,
                new DateTime($event->finishDate),
                $event->ownerId,
                $ownerProjection->email,
                $ownerProjection->firstname,
                $ownerProjection->lastname,
                (int) $event->status,
                $userProjection->id === $event->ownerId,
            ));
        }
    }

    /**
     * @throws \Exception
     */
    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->name = $event->name;
            $projection->finishDate = new DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        $userProjection = $this->userRepository->findById($event->ownerId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        foreach ($projections as $projection) {
            $projection->ownerId = $event->ownerId;
            $projection->ownerEmail = $userProjection->email;
            $projection->ownerFirstname = $userProjection->firstname;
            $projection->ownerLastname = $userProjection->lastname;
            $projection->isOwner = $projection->userId === $event->ownerId;
            if ($projection->isOwner) {
                $projection->lastRequestStatus = null;
            }
        }
    }

    private function whenProjectStatusChanged(ProjectStatusWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            ++$projection->tasksCount;
        }
    }

    private function whenRequestCreated(RequestWasCreatedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            ++$projection->pendingRequestsCount;
            if ($projection->userId === $event->userId) {
                $projection->lastRequestStatus = (int) $event->status;
            }
        }
    }

    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            --$projection->pendingRequestsCount;
            if ($projection->userId === $event->userId) {
                $projection->lastRequestStatus = (int) $event->status;
            }
        }
    }

    private function whenParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            ++$projection->participantsCount;
            if ($projection->userId === $event->participantId) {
                $projection->isParticipating = true;
            }
        }
    }

    private function whenParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            --$projection->participantsCount;
            if ($projection->userId === $event->participantId) {
                $projection->isParticipating = false;
                $projection->lastRequestStatus = null;
            }
        }
    }

    private function whenUserCreated(UserWasCreatedEvent $event): void
    {
        $ownersProjects = $this->repository->findAllOwnersProjects();

        foreach ($ownersProjects as $ownersProject) {
            $newProjection = clone $ownersProject;
            $newProjection->userId = $event->getAggregateId();
            $newProjection->isOwner = false;
            $newProjection->lastRequestStatus = null;

            $this->unitOfWork->loadProjection($newProjection);
        }
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByOwnerId($event->getAggregateId())
        );
        $projections = $this->unitOfWork->getProjections(
            fn (ProjectListProjection $p) => $p->ownerId === $event->getAggregateId()
        );

        /** @var ProjectListProjection $projection */
        foreach ($projections as $projection) {
            $projection->ownerFirstname = $event->firstname;
            $projection->ownerLastname = $event->lastname;
        }
    }

    /**
     * @return ProjectListProjection[]
     */
    private function getProjectionsById(string $id): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllById($id)
        );

        return $this->unitOfWork->getProjections(
            fn (ProjectListProjection $p) => $p->id === $id
        );
    }
}
