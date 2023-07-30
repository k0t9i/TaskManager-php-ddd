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
use TaskManager\Shared\Domain\Hashable;

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
    private function whenTaskCreated(TaskWasCreatedEvent $event, ?int $version): void
    {
        $projectProjection = $this->projectRepository->findById($event->projectId);
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->projectId, ProjectProjection::class);
        }

        $this->unitOfWork->createProjection(TaskProjection::create(
            $event->getAggregateId(),
            $event->name,
            $event->brief,
            $event->description,
            $event->startDate,
            $event->finishDate,
            $event->ownerId,
            $event->status,
            $projectProjection->getId(),
            $version
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenTaskInformationChanged(TaskInformationWasChangedEvent $event, ?int $version): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->changeInformation(
            $event->name,
            $event->brief,
            $event->description,
            $event->startDate,
            $event->finishDate,
            $version
        );
    }

    private function whenTaskStatusChanged(TaskStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->changeStatus($event->status);
    }

    private function ensureProjectionExists(string $id, ?TaskProjection $projection): void
    {
        if (null === $projection) {
            throw new ProjectionDoesNotExistException($id, TaskProjection::class);
        }
    }

    /**
     * @return TaskProjection
     */
    private function getProjectionById(string $id): Hashable
    {
        $projection = $this->repository->findById($id);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        $result = $this->unitOfWork->findProjection($id);
        $this->ensureProjectionExists($id, $result);

        return $result;
    }
}
