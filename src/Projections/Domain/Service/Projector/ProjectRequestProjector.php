<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\ProjectRequestProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;

final class ProjectRequestProjector extends Projector
{
    /**
     * @var array<array-key, ProjectRequestProjection>
     */
    private array $projections = [];

    public function __construct(
        private readonly ProjectRequestProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projection) {
            $this->repository->save($projection);
        }
    }

    /**
     * @throws \Exception
     */
    private function whenRequestCreated(RequestWasCreatedEvent $event): void
    {
        $userProjection = $this->userRepository->findById($event->userId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->userId, UserProjection::class);
        }

        $projectProjection = $this->projectRepository->findById($event->getAggregateId());
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->getAggregateId(), ProjectProjection::class);
        }

        $this->projections[$event->requestId] = new ProjectRequestProjection(
            $event->requestId,
            $event->userId,
            $userProjection->email,
            $userProjection->firstname,
            $userProjection->lastname,
            $event->status,
            new \DateTime($event->changeDate),
            $event->getAggregateId(),
            $projectProjection->ownerId
        );
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->loadProjectionsAsNeeded($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, ProjectRequestProjection::class);
        }

        $projection->status = $event->status;
        $projection->changeDate = new \DateTime($event->changeDate);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projectionsByUserId = $this->repository->findAllByUserId($event->getAggregateId());

        foreach ($projectionsByUserId as $projectionByUserId) {
            $projection = $this->loadProjectionsAsNeeded($projectionByUserId->id);

            if (null !== $projection) {
                $projection->userFirstname = $event->firstname;
                $projection->userLastname = $event->lastname;
            }
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projectionsByUserId = $this->repository->findAllByProjectId($event->getAggregateId());

        foreach ($projectionsByUserId as $projectionByUserId) {
            $projection = $this->loadProjectionsAsNeeded($projectionByUserId->id);

            if (null !== $projection) {
                $projection->ownerId = $event->ownerId;
            }
        }
    }

    private function loadProjectionsAsNeeded(string $id): ?ProjectRequestProjection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = $this->repository->findById($id);
        }

        return $this->projections[$id];
    }
}
