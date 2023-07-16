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
use TaskManager\Shared\Domain\ValueObject\DateTime;

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

        $this->unitOfWork->createProjection(new UserRequestProjection(
            $event->requestId,
            $event->userId,
            (int) $event->status,
            new DateTime($event->changeDate),
            $event->getAggregateId(),
            $projectProjection->name
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjection($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, UserRequestProjection::class);
        }

        $projection->status = (int) $event->status;
        $projection->changeDate = new DateTime($event->changeDate);
    }

    private function whenProjectInformationChanged(ProjectInformationWasChangedEvent $event): void
    {
        $projections = $this->repository->findAllByProjectId($event->getAggregateId());
        $this->unitOfWork->loadProjections($projections);

        foreach ($projections as $projection) {
            $projection->projectName = $event->name;
        }
    }

    private function getProjection(string $id): ?UserRequestProjection
    {
        /** @var UserRequestProjection $result */
        $result = $this->unitOfWork->findProjection($id);

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findById($id);
    }
}
