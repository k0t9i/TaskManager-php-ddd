<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Projections\Domain\Event\UserWasCreatedEvent;
use TaskManager\Projections\Infrastructure\Repository\DoctrineUserProjectionRepository;

final class UserProjector extends Projector
{
    /**
     * @var array<array-key, UserProjection>
     */
    private array $projections;

    public function __construct(private readonly DoctrineUserProjectionRepository $repository)
    {
    }

    public function flush(): void
    {
        foreach ($this->projections as $projection) {
            $this->repository->save($projection);
        }
    }

    private function whenUserCreated(UserWasCreatedEvent $event): void
    {
        $this->projections[$event->getAggregateId()] = new UserProjection(
            $event->getAggregateId(),
            $event->email,
            $event->firstname,
            $event->lastname
        );
    }

    private function whenUserProfileChanged(UserProfileWasChangedEvent $event): void
    {
        $projection = $this->repository->findById($event->getAggregateId());

        if (null !== $projection) {
            $projection->firstname = $event->firstname;
            $projection->lastname = $event->lastname;

            $this->projections[$event->getAggregateId()] = $projection;
        }
    }
}
