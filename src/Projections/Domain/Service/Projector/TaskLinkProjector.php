<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\TaskLinkProjectionCollection;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projections\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;

final class TaskLinkProjector extends Projector
{
    /**
     * @var array<array-key, TaskLinkProjectionCollection>
     */
    private array $projections = [];

    public function __construct(
        private readonly TaskLinkProjectionRepositoryInterface $repository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
        private readonly TaskProjectionRepositoryInterface $taskRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var TaskLinkProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var TaskLinkProjection $item */
            foreach ($projections->getRemovedItems() as $item) {
                $this->repository->delete($item);
            }

            $projections->flush();
        }
    }

    public function priority(): int
    {
        return 25;
    }

    /**
     * @throws \Exception
     */
    private function whenTaskLinkCreated(TaskLinkWasCreated $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());

        $taskProjection = $this->taskRepository->findById($event->linkedTaskId);
        if (null === $taskProjection) {
            throw new ProjectionDoesNotExistException($event->linkedTaskId, TaskProjection::class);
        }

        $projectProjection = $this->projectRepository->findById($taskProjection->projectId);
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($taskProjection->projectId, ProjectProjection::class);
        }

        $userIds = [$taskProjection->ownerId, $projectProjection->ownerId];

        foreach ($userIds as $userId) {
            $projections->addOrUpdateElement(new TaskLinkProjection(
                $event->getAggregateId(),
                $event->linkedTaskId,
                $taskProjection->name,
                $userId
            ));
        }
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());

        foreach ($projections->getItems() as $projection) {
            $projections->remove($projection->getHash());
        }
    }

    /**
     * @throws \Exception
     */
    private function whenLinkedTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projectionsByLinkedTaskId = $this->repository->findAllByTaskId($event->getAggregateId());

        foreach ($projectionsByLinkedTaskId as $projectionByLinkedTaskId) {
            $projections = $this->loadProjectionsAsNeeded($projectionByLinkedTaskId->taskId);

            /** @var TaskLinkProjection $projection */
            foreach ($projections->getItems() as $projection) {
                $projection->linkedTaskName = $event->name;
            }
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $tasks = $this->taskRepository->findAllByProjectId($event->getAggregateId());
        $uniqueTasks = [];
        foreach ($tasks as $task) {
            $uniqueTasks[$task->id] = $task;
        }

        foreach ($uniqueTasks as $task) {
            $projectionsByTaskId = $this->repository->findAllByTaskId($task->id);

            foreach ($projectionsByTaskId as $projectionByTaskId) {
                $projections = $this->loadProjectionsAsNeeded($projectionByTaskId->taskId);

                $oldProjection = $projections->findFirst();
                if (null === $oldProjection) {
                    continue;
                }

                /** @var TaskLinkProjection $newOwnerProjection */
                $newOwnerProjection = clone $oldProjection;
                $newOwnerProjection->userId = $event->ownerId;
                /** @var TaskLinkProjection $projection */
                foreach ($projections->getItems() as $projection) {
                    if ($projection->userId !== $task->ownerId) {
                        $projections->remove($projection->getHash());
                    }
                }
                $projections->addOrUpdateElement($newOwnerProjection);
            }
        }
    }

    private function loadProjectionsAsNeeded(string $id): TaskLinkProjectionCollection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = new TaskLinkProjectionCollection($this->repository->findAllByTaskId($id));
        }

        return $this->projections[$id];
    }

    private function ensureProjectionExists(string $id, array $items): void
    {
        if (0 === count($items)) {
            throw new ProjectionDoesNotExistException($id, TaskLinkProjection::class);
        }
    }
}
