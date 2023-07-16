<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class TaskProjector extends Projector
{
    public function __construct(
        private readonly TaskProjectionRepositoryInterface $repository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var TaskProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var TaskProjection $item */
        foreach ($this->unitOfWork->getDeletedProjections() as $item) {
            $this->repository->delete($item);
        }

        $this->unitOfWork->flush();
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
        $projectProjection = $this->projectRepository->findById($event->projectId);
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->projectId, ProjectProjection::class);
        }

        $this->unitOfWork->createProjection(new TaskProjection(
            $event->getAggregateId(),
            $event->name,
            $event->brief,
            $event->description,
            new DateTime($event->startDate),
            new DateTime($event->finishDate),
            $event->ownerId,
            (int) $event->status,
            $projectProjection->id
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projection = $this->getProjection($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projection);
        $this->unitOfWork->loadProjection($projection);

        $projection->name = $event->name;
        $projection->brief = $event->brief;
        $projection->description = $event->description;
        $projection->startDate = new DateTime($event->startDate);
        $projection->finishDate = new DateTime($event->finishDate);
    }

    private function whenTaskStatusChanged(TaskStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjection($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projection);
        $this->unitOfWork->loadProjection($projection);

        $projection->status = (int) $event->status;
    }

    private function ensureProjectionExists(string $id, ?TaskProjection $projection): void
    {
        if (null === $projection) {
            throw new ProjectionDoesNotExistException($id, TaskProjection::class);
        }
    }

    private function getProjection(string $id): ?TaskProjection
    {
        /** @var TaskProjection $result */
        $result = $this->unitOfWork->findProjection($id);

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findById($id);
    }
}
