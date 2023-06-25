<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Collection\ProjectProjectionCollection;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projections\Domain\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;

final class ProjectProjector extends Projector
{
    /**
     * @var array<array-key, ProjectProjectionCollection>
     */
    private array $projections = [];

    public function __construct(private readonly ProjectProjectionRepositoryInterface $repository)
    {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projections) {
            /** @var ProjectProjection $item */
            foreach ($projections->getItems() as $item) {
                $this->repository->save($item);
            }

            /** @var ProjectProjection $item */
            foreach ($projections->getRemovedItems() as $item) {
                $this->repository->delete($item);
            }

            $projections->flush();
        }
    }

    /**
     * @throws \Exception
     */
    private function whenProjectCreated(ProjectWasCreatedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $projections->addOrUpdateElement(new ProjectProjection(
            $event->getAggregateId(),
            $event->ownerId,
            $event->name,
            $event->description,
            new \DateTime($event->finishDate),
            $event->ownerId,
            (int) $event->status
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->name = $event->name;
            $projection->description = $event->description;
            $projection->finishDate = new \DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $id = $event->getAggregateId();
        $projections = $this->loadProjectionsAsNeeded($id);
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        $oldOwnerId = null;
        /** @var ProjectProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $oldOwnerId = $projection->ownerId;
            $projection->ownerId = $event->ownerId;
        }

        /** @var ProjectProjection $newOwnerProjection */
        $newOwnerProjection = clone $projections->get($oldOwnerId);
        $newOwnerProjection->userId = $event->ownerId;
        $projections->remove($oldOwnerId);
        $projections->addOrUpdateElement($newOwnerProjection);
    }

    private function whenProjectStatusChanged(ProjectStatusWasChangedEvent $event): void
    {
        $projections = $this->loadProjectionsAsNeeded($event->getAggregateId());
        $this->ensureProjectionExists($event->getAggregateId(), $projections->getItems());

        /** @var ProjectProjection $projection */
        foreach ($projections->getItems() as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function loadProjectionsAsNeeded(string $id): ProjectProjectionCollection
    {
        if (!isset($this->projections[$id])) {
            $this->projections[$id] = new ProjectProjectionCollection($this->repository->findAllById($id));
        }

        return $this->projections[$id];
    }

    private function ensureProjectionExists(string $id, array $items): void
    {
        if (0 === count($items)) {
            throw new ProjectionDoesNotExistException($id, ProjectProjection::class);
        }
    }
}
