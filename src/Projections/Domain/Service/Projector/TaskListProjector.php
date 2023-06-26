<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\TaskListProjectionCollection;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projections\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projections\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class TaskListProjector extends Projector
{
    /**
     * @var array<array-key, TaskListProjectionCollection>
     */
    private array $projections = [];

    public function __construct(
        private readonly TaskListProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var TaskListProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var TaskListProjection $item */
            foreach ($projections->getRemovedItems() as $item) {
                $this->repository->delete($item);
            }

            $projections->flush();
        }
    }

    public function priority(): int
    {
        return 50;
    }

    /**
     * @throws \Exception
     */
    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());

        $projectProjection = $this->projectRepository->findById($event->projectId);
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->projectId, ProjectProjection::class);
        }

        $userProjection = $this->userRepository->findById($event->ownerId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        $userIds = [$event->ownerId, $projectProjection->ownerId];

        foreach ($userIds as $userId) {
            $projections->addOrUpdateElement(new TaskListProjection(
                $event->getAggregateId(),
                $userId,
                $event->name,
                new DateTime($event->startDate),
                new DateTime($event->finishDate),
                $event->ownerId,
                $userProjection->email,
                $userProjection->firstname,
                $userProjection->lastname,
                (int) $event->status,
                $projectProjection->id
            ));
        }
    }

    /**
     * @throws \Exception
     */
    private function whenTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var TaskListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->name = $event->name;
            $projection->startDate = new DateTime($event->startDate);
            $projection->finishDate = new DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projectionsByProjectId = $this->repository->findAllByProjectId($event->getAggregateId());
        foreach ($projectionsByProjectId as $projectionByProjectId) {
            $projections = $this->loadProjectionsAsNeeded($projectionByProjectId->id);

            $oldProjection = $projections->findFirst();
            if (null === $oldProjection) {
                continue;
            }

            /** @var TaskListProjection $newOwnerProjection */
            $newOwnerProjection = clone $oldProjection;
            $newOwnerProjection->userId = $event->ownerId;
            /** @var TaskListProjection $projection */
            foreach ($projections->getItems() as $projection) {
                if ($projection->userId !== $projection->ownerId) {
                    $projections->remove($projection->getHash());
                }
            }
            $projections->addOrUpdateElement($newOwnerProjection);
        }
    }

    private function whenTaskStatusChanged(TaskStatusWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var TaskListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projectionsByOwnerId = $this->repository->findAllByOwnerId($event->getAggregateId());

        foreach ($projectionsByOwnerId as $projectionByOwnerId) {
            $projections = $this->loadProjectionsAsNeeded($projectionByOwnerId->id);

            /** @var TaskListProjection $projection */
            foreach ($projections->getItems() as $projection) {
                $projection->ownerFirstname = $event->firstname;
                $projection->ownerLastname = $event->lastname;
            }
        }
    }

    private function whenTaskLinkCreated(TaskLinkWasCreated $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var TaskListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            ++$projection->linksCount;
        }
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var TaskListProjection $projection */
        foreach ($projections->getItems() as $projection) {
            --$projection->linksCount;
        }
    }

    private function loadProjectionsAsNeeded(string $id): TaskListProjectionCollection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = new TaskListProjectionCollection($this->repository->findAllById($id));
        }

        return $this->projections[$id];
    }

    private function ensureProjectionExists(string $id, array $items): void
    {
        if (0 === count($items)) {
            throw new ProjectionDoesNotExistException($id, TaskListProjection::class);
        }
    }
}
