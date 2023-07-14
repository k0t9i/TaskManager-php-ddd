<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projections\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;

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

        $this->unitOfWork->loadProjection(new TaskLinkProjection(
            $event->getAggregateId(),
            $event->linkedTaskId,
            $taskProjection->name
        ));
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projection = $this->getProjection($event->getAggregateId(), $event->linkedTaskId);
        $this->unitOfWork->deleteProjection($projection);
    }

    /**
     * @throws \Exception
     */
    private function whenLinkedTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByLinkedTaskId($event->getAggregateId())
        );
        $projections = $this->unitOfWork->getProjections(
            fn (TaskLinkProjection $p) => $p->taskId === $event->getAggregateId()
        );

        /** @var TaskLinkProjection $projection */
        foreach ($projections as $projection) {
            $projection->linkedTaskName = $event->name;
        }
    }

    private function getProjection(string $taskId, string $linkedTaskId): ?TaskLinkProjection
    {
        /** @var TaskLinkProjection $result */
        $result = $this->unitOfWork->getProjection(TaskLinkProjection::hash($taskId, $linkedTaskId));

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findByTaskAndLinkedTaskId($taskId, $linkedTaskId);
    }
}
