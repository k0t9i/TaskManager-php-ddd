<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasAddedEvent;
use TaskManager\Projections\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projections\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectProjector extends Projector
{
    public function __construct(
        private readonly ProjectProjectionRepositoryInterface $repository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var ProjectProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
        }

        /** @var ProjectProjection $item */
        foreach ($this->unitOfWork->getDeletedProjections() as $item) {
            $this->repository->delete($item);
        }

        $this->unitOfWork->flush();
    }

    /**
     * @throws \Exception
     */
    private function whenProjectCreated(ProjectWasCreatedEvent $event): void
    {
        $this->unitOfWork->createProjection(new ProjectProjection(
            $event->getAggregateId(),
            $event->ownerId,
            $event->name,
            $event->description,
            new DateTime($event->finishDate),
            $event->ownerId,
            (int) $event->status
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->name = $event->name;
            $projection->description = $event->description;
            $projection->finishDate = new DateTime($event->finishDate);
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        $oldProjection = null;
        $newProjection = null;
        foreach ($projections as $projection) {
            if ($projection->userId === $projection->ownerId) {
                $oldProjection = $projection;
            }
            if ($projection->userId === $event->ownerId) {
                $newProjection = $projection;
                $newProjection->isOwner = true;
            }
            $projection->ownerId = $event->ownerId;
        }

        $this->unitOfWork->deleteProjection($oldProjection);
        $this->unitOfWork->createProjection($newProjection);
    }

    private function whenProjectStatusChanged(ProjectStatusWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->status = (int) $event->status;
        }
    }

    private function whenParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        if (0 === count($projections)) {
            return;
        }

        foreach ($projections as $projection) {
            if ($projection->userId === $event->participantId) {
                $projection->userId = $event->participantId;
                $projection->isOwner = false;
                $this->unitOfWork->createProjection($projection);
                return;
            }
        }

        $newProjection = clone array_pop($projections);
        $newProjection->userId = $event->participantId;
        $newProjection->isOwner = false;
        $this->unitOfWork->createProjection($newProjection);
    }

    private function whenParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projection = $this->unitOfWork->findProjection(
            ProjectProjection::hash($event->getAggregateId(), $event->participantId)
        );
        if (null === $projection) {
            $projection = $this->repository->findByIdAndUserId($event->getAggregateId(), $event->participantId);
        }
        if (null === $projection) {
            return;
        }

        $this->unitOfWork->deleteProjection($projection);
    }

    /**
     * @return ProjectProjection[]
     */
    private function getProjectionsById(string $id): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllById($id)
        );

        return $this->unitOfWork->findProjections(
            fn (ProjectProjection $p) => $p->id === $id
        );
    }
}
