<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\TaskProjectionCollection;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;

final class TaskProjector extends Projector
{
    /**
     * @var array<array-key, TaskProjectionCollection>
     */
    private array $projections = [];

    public function __construct(
        private readonly TaskProjectionRepositoryInterface $repository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var TaskProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var TaskProjection $item */
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

        $userIds = [$event->ownerId, $projectProjection->ownerId];

        foreach ($userIds as $userId) {
            $projections->addOrUpdateElement(new TaskProjection(
                $event->getAggregateId(),
                $userId,
                $event->name,
                $event->brief,
                $event->description,
                new \DateTime($event->startDate),
                new \DateTime($event->finishDate),
                $event->ownerId,
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

        /** @var TaskProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->name = $event->name;
            $projection->brief = $event->brief;
            $projection->description = $event->description;
            $projection->startDate = new \DateTime($event->startDate);
            $projection->finishDate = new \DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projectionsByProjectId = $this->repository->findAllByProjectId($event->getAggregateId());
        foreach ($projectionsByProjectId as $projectionByProjectId) {
            $projections = $this->loadProjectionsAsNeeded($projectionByProjectId->id);

            $oldProjection = $projections->findFirst();
            if (null === $oldProjection) {
                throw new ProjectionDoesNotExistException($projectionByProjectId->id, TaskProjection::class);
            }

            /** @var TaskProjection $newOwnerProjection */
            $newOwnerProjection = clone $oldProjection;
            $newOwnerProjection->userId = $event->ownerId;
            /** @var TaskProjection $projection */
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

        /** @var TaskProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function loadProjectionsAsNeeded(string $id): TaskProjectionCollection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = new TaskProjectionCollection($this->repository->findAllById($id));
        }

        return $this->projections[$id];
    }

    private function ensureProjectionExists(string $id, array $items): void
    {
        if (0 === count($items)) {
            throw new ProjectionDoesNotExistException($id, TaskProjection::class);
        }
    }
}
