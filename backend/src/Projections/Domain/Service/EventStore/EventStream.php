<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;

final class EventStream implements EventStreamInterface
{
    private int $cursor = 0;

    /**
     * @var DomainEventEnvelope[]
     */
    private readonly array $events;

    /**
     * @param DomainEventEnvelope[] $events
     */
    public function __construct(array $events)
    {
        $this->events = array_values($events);
    }

    public function next(): ?DomainEventEnvelope
    {
        return $this->events[$this->cursor++] ?? null;
    }

    public function eventCount(): int
    {
        return count($this->events);
    }
}
