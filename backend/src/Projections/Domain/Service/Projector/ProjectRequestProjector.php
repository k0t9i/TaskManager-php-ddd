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
use TaskManager\Shared\Domain\ValueObject\DateTime;

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

        $this->unitOfWork->createProjection(new ProjectRequestProjection(
            $event->requestId,
            $event->userId,
            $userProjection->email,
            $userProjection->firstname,
            $userProjection->lastname,
            (int) $event->status,
            new DateTime($event->changeDate),
            $event->getAggregateId()
        ));
    }

    /**
     * @throws \Exception
     */
    private function whenRequestStatusChanged(RequestStatusWasChangedEvent $event): void
    {
        $projection = $this->getProjection($event->requestId);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($event->requestId, ProjectRequestProjection::class);
        }

        $projection->status = (int) $event->status;
        $projection->changeDate = new DateTime($event->changeDate);
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projections = $this->repository->findAllByUserId($event->getAggregateId());
        $this->unitOfWork->loadProjections($projections);

        foreach ($projections as $projection) {
            $projection->userFirstname = $event->firstname;
            $projection->userLastname = $event->lastname;
        }
    }

    private function getProjection(string $id): ?ProjectRequestProjection
    {
        /** @var ProjectRequestProjection $result */
        $result = $this->unitOfWork->findProjection($id);

        if (null !== $result) {
            return $result;
        }

        return $this->repository->findById($id);
    }
}
