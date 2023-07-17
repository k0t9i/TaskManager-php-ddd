<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\EventStreamInfoDTO;
use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Shared\Domain\Service\DomainEventFactoryInterface;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final readonly class EventStore implements EventStoreInterface
{
    public function __construct(
        private DomainEventFactoryInterface $eventFactory,
        private EventRepositoryInterface $repository,
        private EventStreamFactoryInterface $streamFactory
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getStreamInfo(?DateTime $lastDatetime): EventStreamInfoDTO
    {
        $events = $this->repository->findOrderedFromLastTime($lastDatetime);

        /** @var DomainEventInterface[] $domainEvents */
        $domainEvents = [];
        foreach ($events as $event) {
            $domainEvents = array_merge($domainEvents, $event->createDomainEvents($this->eventFactory));
        }

        $position = $lastDatetime;
        if (count($domainEvents) > 0) {
            $position = new DateTime(array_reverse($domainEvents)[0]->getOccurredOn());
        }

        return new EventStreamInfoDTO(
            $this->streamFactory->createStream($domainEvents),
            $position
        );
    }
}
