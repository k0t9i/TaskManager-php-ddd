<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;
use TaskManager\Shared\Infrastructure\Service\DomainEventFactoryInterface;

final readonly class EventStore implements EventStoreInterface
{
    public function __construct(
        private DomainEventFactoryInterface $eventFactory,
        private EventRepositoryInterface $repository,
        private EventStreamFactoryInterface $streamFactory
    ) {
    }

    public function getStream(\DateTimeImmutable $lastDatetime): EventStreamInterface
    {
        $events = $this->repository->findOrderedFromLastTime($lastDatetime);

        $domainEvents = [];
        foreach ($events as $event) {
            $domainEvents = array_merge($domainEvents, $event->createDomainEvents($this->eventFactory));
        }

        return $this->streamFactory->createStream($domainEvents);
    }
}
