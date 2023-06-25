<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Projections\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserRequestProjectionRepositoryInterface;

final class UserRequestProjector extends Projector
{
    /**
     * @var array<array-key, UserRequestProjection>
     */
    private array $projections = [];

    public function __construct(
        private readonly UserRequestProjectionRepositoryInterface $repository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
    ) {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projection) {
            $this->repository->save($projection);
        }
    }

    public function priority(): int
    {
        return 50;
    }

    /**
     * @throws \Exception
     */
    private function whenRequestCreated(RequestWasCreatedEvent $event): void
    {
        $projectProjection = $this->projectRepository->findById($event->getAggregateId());
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->getAggregateId(), ProjectProjection::class);
        }

        $this->projections[$event->requestId] = new UserRequestProjection(
            $event->requestId,
            $event->userId,
            $event->status,
            new \DateTime($event->changeDate),
            $event->getAggregateId(),
            $projectProjection->name
        );
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->loadProjectionsAsNeeded($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, UserRequestProjection::class);
        }

        $projection->status = $event->status;
        $projection->changeDate = new \DateTime($event->changeDate);
    }

    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projectionsByProjectId = $this->repository->findAllByProjectId($event->getAggregateId());

        foreach ($projectionsByProjectId as $projectionByProjectId) {
            $projection = $this->loadProjectionsAsNeeded($projectionByProjectId->id);

            if (null !== $projection) {
                $projection->projectName = $event->name;
            }
        }
    }

    private function loadProjectionsAsNeeded(string $id): ?UserRequestProjection
    {
        if (!isset($this->projections[$id])) {
            $projection = $this->repository->findById($id);
            if (null !== $projection) {
                $this->projections[$id] = $projection;
            }
        }

        return $this->projections[$id];
    }
}
