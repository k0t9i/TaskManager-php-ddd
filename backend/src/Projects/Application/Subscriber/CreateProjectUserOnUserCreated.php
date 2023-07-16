<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Subscriber;

use TaskManager\Projects\Domain\Entity\ProjectUser;
use TaskManager\Projects\Domain\Event\ProjectUserWasCreatedEvent;
use TaskManager\Projects\Domain\Repository\ProjectUserRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;

final readonly class CreateProjectUserOnUserCreated implements DomainEventSubscriberInterface
{
    public function __construct(private ProjectUserRepositoryInterface $repository)
    {
    }

    public function __invoke(ProjectUserWasCreatedEvent $event): void
    {
        $user = new ProjectUser(
            new ProjectUserId($event->getAggregateId())
        );

        $this->repository->save($user);
    }
}
