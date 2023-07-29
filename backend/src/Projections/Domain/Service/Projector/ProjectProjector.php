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
use TaskManager\Shared\Domain\Hashable;

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
        $this->unitOfWork->createProjection(ProjectProjection::create(
            $event->getAggregateId(),
            $event->ownerId,
            $event->name,
            $event->description,
            $event->finishDate,
            $event->ownerId,
            $event->status
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeInformation(
                $event->name,
                $event->description,
                $event->finishDate
            );
        }
    }

    private function whenProjectOwnerChanged(ProjectOwnerWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeOwner($event->ownerId);
            if ($projection->userId === $event->ownerId) {
                $this->unitOfWork->undeleteProjection($projection);
            }
        }
    }

    private function whenProjectStatusChanged(ProjectStatusWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsById($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeStatus($event->status);
        }
    }

    private function whenParticipantAdded(ProjectParticipantWasAddedEvent $event): void
    {
        $projection = $this->getProjectionById($event->getAggregateId());

        if (null === $projection) {
            return;
        }

        $this->unitOfWork->createProjection(
            $projection->cloneForUser($event->participantId)
        );
    }

    private function whenParticipantRemoved(ProjectParticipantWasRemovedEvent $event): void
    {
        $projection = $this->getProjectionByIdAndUserId($event->getAggregateId(), $event->participantId);
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
            fn (ProjectProjection $p) => $p->getId() === $id
        );
    }

    /**
     * @return ProjectProjection|null
     */
    private function getProjectionByIdAndUserId(string $id, string $userId): ?Hashable
    {
        $projection = $this->repository->findByIdAndUserId($id, $userId);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        return $this->unitOfWork->findProjection(
            ProjectProjection::hash($id, $userId)
        );
    }

    /**
     * @return ProjectProjection|null
     */
    private function getProjectionById(string $id): ?Hashable
    {
        $projection = $this->repository->findById($id);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        $projections = $this->unitOfWork->findProjections(
            fn (ProjectProjection $p) => $p->getId() === $id
        );

        if (0 === count($projections)) {
            return null;
        }

        return array_values($projections)[0];
    }
}
