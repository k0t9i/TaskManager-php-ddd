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
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\Hashable;

final class UserRequestProjector extends Projector
{
    public function __construct(
        private readonly UserRequestProjectionRepositoryInterface $repository,
        private readonly ProjectProjectionRepositoryInterface $projectRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var UserRequestProjection $item */
        foreach ($this->unitOfWork->getProjections() as $item) {
            $this->repository->save($item);
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
    private function whenRequestCreated(RequestWasCreatedEvent $event): void
    {
        $projectProjection = $this->projectRepository->findById($event->getAggregateId());
        if (null === $projectProjection) {
            throw new ProjectionDoesNotExistException($event->getAggregateId(), ProjectProjection::class);
        }

        $this->unitOfWork->createProjection(UserRequestProjection::create(
            $event->requestId,
            $event->userId,
            $event->status,
            $event->changeDate,
            $event->getAggregateId(),
            $projectProjection->getName()
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjectionById($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, UserRequestProjection::class);
        }

        $projection->changeStatus($event->status, $event->changeDate);
    }

    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByProjectId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeProjectInformation($event->name);
        }
    }

    /**
     * @return UserRequestProjection|null
     */
    private function getProjectionById(string $id): ?Hashable
    {
        $projection = $this->repository->findById($id);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        return $this->unitOfWork->findProjection($id);
    }

    /**
     * @return UserRequestProjection[]
     */
    private function getProjectionsByProjectId(string $projectId): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByProjectId($projectId)
        );

        return $this->unitOfWork->findProjections(
            fn (UserRequestProjection $p) => $p->isForProject($projectId)
        );
    }
}
