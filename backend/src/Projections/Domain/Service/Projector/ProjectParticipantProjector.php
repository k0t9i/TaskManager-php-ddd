<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasAddedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectParticipantProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\Hashable;

final class ProjectParticipantProjector extends Projector
{
    public function __construct(
        private readonly ProjectParticipantProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly TaskListProjectionRepositoryInterface $taskRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var ProjectParticipantProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var ProjectParticipantProjection $item */
        foreach ($this->unitOfWork->getDeletedProjections() as $item) {
            $this->repository->delete($item);
        }

        $this->unitOfWork->flush();
    }

    public function priority(): int
    {
        return 50;
    }

    private function whenProjectParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $userProjection = $this->userRepository->findById($event->participantId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->participantId, UserProjection::class);
        }

        $tasksCount = $this->taskRepository->countByProjectAndOwnerId($event->getAggregateId(), $event->participantId);

        $this->unitOfWork->createProjection(ProjectParticipantProjection::create(
            $event->participantId,
            $event->getAggregateId(),
            $userProjection->getEmail(),
            $userProjection->getFirstname(),
            $userProjection->getLastname(),
            $tasksCount
        ));
    }

    private function whenProjectParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projection = $this->getProjectionByProjectAndUserId($event->getAggregateId(), $event->participantId);
        if (null === $projection) {
            return;
        }

        $this->unitOfWork->deleteProjection($projection);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByUserId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeUserInformation($event->firstname, $event->lastname);
        }
    }

    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projection = $this->getProjectionByProjectAndUserId($event->projectId, $event->ownerId);
        if (null === $projection) {
            return;
        }

        $projection->addTask();
    }

    /**
     * @return ProjectParticipantProjection[]
     */
    private function getProjectionsByUserId(string $userId): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByUserId($userId)
        );

        return $this->unitOfWork->findProjections(
            fn (ProjectParticipantProjection $p) => $p->isForUser($userId)
        );
    }

    /**
     * @return ProjectParticipantProjection|null
     */
    private function getProjectionByProjectAndUserId(string $projectId, string $userId): ?Hashable
    {
        $projection = $this->repository->findByProjectAndUserId($projectId, $userId);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        return $this->unitOfWork->findProjection(
            ProjectParticipantProjection::hash($projectId, $userId)
        );
    }
}
