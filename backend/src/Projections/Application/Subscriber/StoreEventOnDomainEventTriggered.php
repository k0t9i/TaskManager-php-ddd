<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Subscriber;

use TaskManager\Projections\Domain\Entity\Event;
use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;
use TaskManager\Shared\Domain\Event\DomainEventInterface;

final readonly class StoreEventOnDomainEventTriggered implements DomainEventSubscriberInterface
{
    public function __construct(
        private EventRepositoryInterface $repository,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(DomainEventInterface $domainEvent): void
    {
        $event = Event::fromDomainEvent($this->uuidGenerator->generate(), $domainEvent);

        $this->repository->save($event);
    }
}
