<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\ProjectParticipantProjectionCollection;
use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasAddedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projections\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectParticipantProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;

final class ProjectParticipantProjector extends Projector
{
    /**
     * @var array<array-key, ProjectParticipantProjectionCollection>
     */
    private array $projections = [];

    public function __construct(
        private readonly ProjectParticipantProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var ProjectParticipantProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var ProjectParticipantProjection $item */
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

    private function whenProjectParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $userProjection = $this->userRepository->findById($event->participantId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->participantId, UserProjection::class);
        }

        $projections = $this->loadProjectionsAsNeeded($event->participantId);
        $projections->addOrUpdateElement(new ProjectParticipantProjection(
            $event->participantId,
            $event->getAggregateId(),
            $userProjection->email,
            $userProjection->firstname,
            $userProjection->lastname
        ));
    }

    private function whenProjectParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $userProjection = $this->userRepository->findById($event->participantId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->participantId, UserProjection::class);
        }

        $projections = $this->loadProjectionsAsNeeded($event->participantId);
        $projections->remove($event->getAggregateId());
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());

        /** @var ProjectParticipantProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->userFirstname = $event->firstname;
            $projection->userLastname = $event->lastname;
        }
    }

    private function whenTaskCreated(TaskWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->ownerId);

        /** @var ProjectParticipantProjection $projection */
        foreach ($projections->getItems() as $projection) {
            if ($projection->projectId === $event->projectId) {
                ++$projection->tasksCount;
            }
        }
    }

    private function loadProjectionsAsNeeded(string $userId): ProjectParticipantProjectionCollection
    {
        if (!isset($this->projections[$userId])) {
            $this->projections[$userId] = new ProjectParticipantProjectionCollection(
                $this->repository->findAllByProjectId($userId)
            );
        }

        return $this->projections[$userId];
    }
}
