<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
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
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class TaskListProjector extends Projector
{
    public function __construct(
        private readonly TaskListProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var TaskListProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var TaskListProjection $item */
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

        $userProjection = $this->userRepository->findById($event->ownerId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->ownerId, UserProjection::class);
        }

        $this->unitOfWork->loadProjection(new TaskListProjection(
            $event->getAggregateId(),
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

    /**
     * @throws \Exception
     */
    private function whenTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projection = $this->getProjection($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projection);
        $this->unitOfWork->loadProjection($projection);

        $projection->name = $event->name;
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

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByOwnerId($event->getAggregateId())
        );
        $projections = $this->unitOfWork->getProjections(
            fn (TaskListProjection $p) => $p->ownerId === $event->getAggregateId()
        );

        /** @var TaskListProjection $projection */
        foreach ($projections as $projection) {
            $projection->ownerFirstname = $event->firstname;
            $projection->ownerLastname = $event->lastname;
        }
    }

    private function whenTaskLinkCreated(TaskLinkWasCreated $event): void
    {
        $projection = $this->getProjection($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projection);
        $this->unitOfWork->loadProjection($projection);

        ++$projection->linksCount;
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projection = $this->getProjection($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projection);
        $this->unitOfWork->loadProjection($projection);

        --$projection->linksCount;
    }

    private function ensureProjectionExists(string $id, ?TaskListProjection $projection): void
    {
        if (null === $projection) {
            throw new ProjectionDoesNotExistException($id, TaskListProjection::class);
        }
    }

    private function getProjection(string $id): ?TaskListProjection
    {
        /** @var TaskListProjection $result */
        $result = $this->unitOfWork->getProjection($id);

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findById($id);
    }
}
