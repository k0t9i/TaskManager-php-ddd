<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\ProjectListProjectionCollection;
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
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectListProjector extends Projector
{
    /**
     * @var array<array-key, ProjectListProjectionCollection>
     */
    private array $projections = [];

    public function __construct(
        private readonly ProjectListProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var ProjectListProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var ProjectListProjection $item */
            foreach ($projections->getRemovedItems() as $item) {
                $this->repository->delete($item);
            }

            $projections->flush();
        }
    }

    /**
     * @throws \Exception
     */
    private function whenProjectCreated(ProjectWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());

        $ownerProjection = $this->userRepository->findById($event->ownerId);
        if (null === $ownerProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        $userProjections = $this->userRepository->findAll();

        foreach ($userProjections as $userProjection) {
            $projections->addOrUpdateElement(new ProjectListProjection(
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
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->name = $event->name;
            $projection->finishDate = new DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $id = $event->getAggregateId();
        $projections = $this->loadProjectionsAsNeeded($id);
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        $userProjection = $this->userRepository->findById($event->ownerId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
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
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->projectId);
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            ++$projection->tasksCount;
        }
    }

    private function whenRequestCreated(RequestWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            ++$projection->pendingRequestsCount;
            if ($projection->userId === $event->userId) {
                $projection->lastRequestStatus = (int) $event->status;
            }
        }
    }

    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            --$projection->pendingRequestsCount;
            if ($projection->userId === $event->userId) {
                $projection->lastRequestStatus = (int) $event->status;
            }
        }
    }

    private function whenParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $existingProjection */
        $existingProjection = $projections->findFirst();
        if (null === $existingProjection) {
            throw new \RuntimeException(sprintf('Project "%s" does not exist.', $event->getAggregateId()));
        }

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            ++$projection->participantsCount;
            if ($projection->userId === $event->participantId) {
                $projection->isParticipating = true;
            }
        }
    }

    private function whenParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $existingProjection */
        $existingProjection = $projections->findFirst();
        if (null === $existingProjection) {
            throw new \RuntimeException(sprintf('Project "%s" does not exist.', $event->getAggregateId()));
        }

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            --$projection->participantsCount;
            if ($projection->userId === $event->participantId) {
                $projection->isParticipating = false;
            }
        }
    }

    private function whenUserCreated(UserWasCreatedEvent $event): void
    {
        $ownersProjects = $this->repository->findAllOwnersProjects();

        foreach ($ownersProjects as $ownersProject) {
            $id = $ownersProject->id;
            if (!isset($this->projections[$id])) {
                $this->projections[$id] = new ProjectListProjectionCollection([$ownersProject]);
            }

            $newProjection = clone $ownersProject;
            $newProjection->userId = $event->getAggregateId();
            $newProjection->isOwner = false;
            $newProjection->lastRequestStatus = null;

            $this->projections[$id]->addOrUpdateElement($newProjection);
        }
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projectionsByOwnerId = $this->repository->findAllByOwnerId($event->getAggregateId());

        foreach ($projectionsByOwnerId as $projectionByOwnerId) {
            $projections = $this->loadProjectionsAsNeeded($projectionByOwnerId->id);

            /** @var ProjectListProjection $projection */
            foreach ($projections->getItems() as $projection) {
                $projection->ownerFirstname = $event->firstname;
                $projection->ownerLastname = $event->lastname;
            }
        }
    }

    private function loadProjectionsAsNeeded(string $id): ProjectListProjectionCollection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = new ProjectListProjectionCollection($this->repository->findAllById($id));
        }

        return $this->projections[$id];
    }

    private function ensureProjectionExists(string $id, array $items): void
    {
        if (0 === count($items)) {
            throw new ProjectionDoesNotExistException($id, ProjectListProjection::class);
        }
    }
}
