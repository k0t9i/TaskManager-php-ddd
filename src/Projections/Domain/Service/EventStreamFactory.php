<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

final readonly class EventStreamFactory implements EventStreamFactoryInterface
{
    public function __construct(private ?EventStreamFilterInterface $streamFilter = null)
    {
    }

    /**
     * @param DomainEventInterface[] $events
     */
    public function createStream(array $events): EventStreamInterface
    {
        return new EventStream($this->filterEvents($events));
    }

    /**
     * @param DomainEventInterface[] $events
     *
     * @return DomainEventInterface[]
     */
    private function filterEvents(array $events): array
    {
        if (null === $this->streamFilter) {
            return $events;
        }

        $result = [];
        foreach ($events as $key => $event) {
            if ($this->streamFilter->isSuitable($event)) {
                $result[$key] = $event;
            }
        }

        return $result;
    }
}
