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
use TaskManager\Shared\Domain\Hashable;

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

        $this->unitOfWork->createProjection(TaskListProjection::create(
            $event->getAggregateId(),
            $event->name,
            $event->startDate,
            $event->finishDate,
            $event->ownerId,
            $userProjection->getFullName(),
            $event->status,
            $projectProjection->getId()
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenTaskInformationChanged(TaskInformationWasChangedEvent $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->changeInformation($event->name, $event->startDate, $event->finishDate);
    }

    private function whenTaskStatusChanged(TaskStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->changeStatus($event->status);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByOwnerId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeOwnerInformation(UserProjection::fullName($event->firstname, $event->lastname));
        }
    }

    private function whenTaskLinkCreated(TaskLinkWasCreated $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->createLink();
    }

    private function whenTaskLinkDeleted(TaskLinkWasDeleted $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        $projection->deleteLink();
    }

    private function ensureProjectionExists(string $id, ?TaskListProjection $projection): void
    {
        if (null === $projection) {
            throw new ProjectionDoesNotExistException($id, TaskListProjection::class);
        }
    }

    /**
     * @return TaskListProjection
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

    /**
     * @return TaskListProjection[]
     */
    private function getProjectionsByOwnerId(string $ownerId): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByOwnerId($ownerId)
        );

        return $this->unitOfWork->findProjections(
            fn (TaskListProjection $p) => $p->isUserOwner($ownerId)
        );
    }
}
