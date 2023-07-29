<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Event\UserWasCreatedEvent;
use TaskManager\Projections\Domain\Exception\ProjectionDoesNotExistException;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;

final class UserProjector extends Projector
{
    /**
     * @var array<array-key, UserProjection>
     */
    private array $projections = [];

    public function __construct(private readonly UserProjectionRepositoryInterface $repository)
    {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projection) {
            $this->repository->save($projection);
        }
    }

    public function priority(): int
    {
        return 200;
    }

    private function whenUserCreated(UserWasCreatedEvent $event): void
    {
        $this->projections[$event->getAggregateId()] = UserProjection::create(
            $event->getAggregateId(),
            $event->email,
            $event->firstname,
            $event->lastname
        );
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $id = $event->getAggregateId();
        $projection = $this->projections[$id] ?? $this->repository->findById($id);

        if (null === $projection) {
            throw new ProjectionDoesNotExistException($id, UserProjection::class);
        }

        $projection->changeInformation($event->firstname, $event->lastname);

        $this->projections[$id] = $projection;
    }
}
