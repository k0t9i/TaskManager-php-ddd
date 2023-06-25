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
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;

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

        $userProjection = $this->userRepository->findById($event->ownerId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        $projections->addOrUpdateElement(new ProjectListProjection(
            $event->getAggregateId(),
            $event->ownerId,
            $event->name,
            new \DateTime($event->finishDate),
            $event->ownerId,
            $userProjection->email,
            $userProjection->firstname,
            $userProjection->lastname,
            (int) $event->status
        ));
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
            $projection->finishDate = new \DateTime($event->finishDate);
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

        $oldOwnerId = null;
        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $oldOwnerId = $projection->ownerId;
            $projection->ownerId = $event->ownerId;
            $projection->ownerEmail = $userProjection->email;
            $projection->ownerFirstname = $userProjection->firstname;
            $projection->ownerLastname = $userProjection->lastname;
        }

        /** @var ProjectListProjection $newOwnerProjection */
        $newOwnerProjection = clone $projections->get($oldOwnerId);
        $newOwnerProjection->userId = $event->ownerId;
        $projections->remove($oldOwnerId);
        $projections->addOrUpdateElement($newOwnerProjection);
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
        }
    }

    private function whenRequestStatusChange(RequestStatusWasChangedEvent $event): void
    {
        // TODO use class constant
        if ('0' === $event->status) {
            return;
        }

        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            --$projection->pendingRequestsCount;
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

        $newProjection = clone $existingProjection;
        $newProjection->userId = $event->participantId;

        $projections->addOrUpdateElement($newProjection);

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            ++$projection->participantsCount;
        }
    }

    private function whenParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        $projections->remove($event->participantId);

        /** @var ProjectListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            --$projection->participantsCount;
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