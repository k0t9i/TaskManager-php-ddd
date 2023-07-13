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
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;

final class ProjectParticipantProjector extends Projector
{
    public function __construct(
        private readonly ProjectParticipantProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
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

        $this->unitOfWork->loadProjection(new ProjectParticipantProjection(
            $event->participantId,
            $event->getAggregateId(),
            $userProjection->email,
            $userProjection->firstname,
            $userProjection->lastname
        ));
    }

    private function whenProjectParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projection = $this->getProjection($event->getAggregateId(), $event->participantId);
        $this->unitOfWork->deleteProjection($projection);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->repository->findAllByUserId($event->getAggregateId());
        $this->unitOfWork->loadProjections($projections);

        /** @var ProjectParticipantProjection $projection */
        foreach ($this->unitOfWork->getProjections() as $projection) {
            if ($projection->userId === $event->getAggregateId()) {
                $projection->userFirstname = $event->firstname;
                $projection->userLastname = $event->lastname;
            }
        }
    }

    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projection = $this->getProjection($event->projectId, $event->ownerId);
        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
            ++$projection->tasksCount;
        }
    }

    private function getProjection(string $projectId, string $userId): ?ProjectParticipantProjection
    {
        /** @var ProjectParticipantProjection $result */
        $result = $this->unitOfWork->getProjection(ProjectParticipantProjection::hash($projectId, $userId));

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findByProjectAndUserId($projectId, $userId);
    }
}
