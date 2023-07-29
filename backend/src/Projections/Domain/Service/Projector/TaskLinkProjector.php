<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projections\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projections\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\Hashable;

final class TaskLinkProjector extends Projector
{
    public function __construct(
        private readonly TaskLinkProjectionRepositoryInterface $repository,
        private readonly TaskProjectionRepositoryInterface $taskRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var TaskLinkProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var TaskLinkProjection $item */
        foreach ($this->unitOfWork->getDeletedProjections() as $item) {
            $this->repository->delete($item);
        }

        $this->unitOfWork->flush();
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
        $taskProjection = $this->taskRepository->findById($event->linkedTaskId);
        if (null === $taskProjection) {
            throw new ProjectionDoesNotExistException($event->linkedTaskId, TaskProjection::class);
        }

        $this->unitOfWork->createProjection(TaskLinkProjection::create(
            $event->getAggregateId(),
            $event->linkedTaskId,
            $taskProjection->getName(),
            $taskProjection->getStatus()
        ));
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projection = $this->getProjectionTaskAndLinkedTaskId($event->getAggregateId(), $event->linkedTaskId);
        $this->unitOfWork->deleteProjection($projection);
    }

    /**
     * @throws \Exception
     */
    private function whenLinkedTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByLinkedTaskId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeLinkedTaskInformation($event->name);
        }
    }

    /**
     * @throws \Exception
     */
    private function whenLinkedTaskStatusChanged(TaskStatusWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByLinkedTaskId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeLinkedTaskStatus($event->status);
        }
    }

    /**
     * @return TaskLinkProjection|null
     */
    private function getProjectionTaskAndLinkedTaskId(string $taskId, string $linkedTaskId): ?Hashable
    {
        $projection = $this->repository->findByTaskAndLinkedTaskId($taskId, $linkedTaskId);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        return $this->unitOfWork->findProjection(
            TaskLinkProjection::hash($taskId, $linkedTaskId)
        );
    }

    /**
     * @return TaskLinkProjection[]
     */
    private function getProjectionsByLinkedTaskId(string $taskId): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByLinkedTaskId($taskId)
        );

        return $this->unitOfWork->findProjections(
            fn (TaskLinkProjection $p) => $p->isLinkedTask($taskId)
        );
    }
}
