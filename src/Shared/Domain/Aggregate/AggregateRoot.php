<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Aggregate;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

abstract class AggregateRoot
{
    /**
     * @var DomainEventInterface[]
     */
    private array $events = [];

    public function registerEvent(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return DomainEventInterface[]
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
