<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

final class EventStream implements EventStreamInterface
{
    private int $cursor = 0;

    /**
     * @var DomainEventInterface[]
     */
    private readonly array $events;

    /**
     * @param DomainEventInterface[] $events
     */
    public function __construct(array $events)
    {
        $this->events = array_values($events);
    }

    public function next(): ?DomainEventInterface
    {
        return $this->events[$this->cursor++] ?? null;
    }
}
