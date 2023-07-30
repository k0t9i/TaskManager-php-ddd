<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;
use TaskManager\Projections\Domain\DTO\EventStreamInfoDTO;
use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;
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

        /** @var DomainEventEnvelope[] $envelopes */
        $envelopes = [];
        foreach ($events as $event) {
            $envelopes = array_merge($envelopes, $event->createEventEnvelope($this->eventFactory));
        }

        $position = $lastDatetime;
        if (count($envelopes) > 0) {
            $position = new DateTime(array_reverse($envelopes)[0]->event->getOccurredOn());
        }

        return new EventStreamInfoDTO(
            $this->streamFactory->createStream($envelopes),
            $position
        );
    }
}
