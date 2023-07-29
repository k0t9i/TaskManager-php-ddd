<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projections\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectRequestProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Service\ProjectorUnitOfWork;
use TaskManager\Shared\Domain\Hashable;

final class ProjectRequestProjector extends Projector
{
    /**
     * @var array<array-key, ProjectRequestProjection>
     */
    private array $projections = [];

    public function __construct(
        private readonly ProjectRequestProjectionRepositoryInterface $repository,
        private readonly UserProjectionRepositoryInterface $userRepository,
        private readonly ProjectorUnitOfWork $unitOfWork
    ) {
    }

    public function flush(): void
    {
        /** @var ProjectRequestProjection $item */
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
        $userProjection = $this->userRepository->findById($event->userId);
        if (null === $userProjection) {
            throw new ProjectionDoesNotExistException($event->userId, UserProjection::class);
        }

        $this->unitOfWork->createProjection(ProjectRequestProjection::create(
            $event->requestId,
            $event->userId,
            $userProjection->getFullName(),
            $event->status,
            $event->changeDate,
            $event->getAggregateId()
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjectionById($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, ProjectRequestProjection::class);
        }

        $projection->changeStatus($event->status, $event->changeDate);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->getProjectionsByUserId($event->getAggregateId());

        foreach ($projections as $projection) {
            $projection->changeUserInformation(UserProjection::fullName($event->firstname, $event->lastname));
        }
    }

    /**
     * @return ProjectRequestProjection[]
     */
    private function getProjectionsByUserId(string $userId): array
    {
        $this->unitOfWork->loadProjections(
            $this->repository->findAllByUserId($userId)
        );

        return $this->unitOfWork->findProjections(
            fn (ProjectRequestProjection $p) => $p->getUserId() === $userId
        );
    }

    /**
     * @return ProjectRequestProjection|null
     */
    private function getProjectionById(string $id): ?Hashable
    {
        $projection = $this->repository->findById($id);

        if (null !== $projection) {
            $this->unitOfWork->loadProjection($projection);
        }

        return $this->unitOfWork->findProjection($id);
    }
}
