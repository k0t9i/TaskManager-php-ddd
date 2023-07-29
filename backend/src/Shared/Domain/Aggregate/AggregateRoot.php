<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Aggregate;

use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Shared\Domain\ValueObject\Uuid;

abstract class AggregateRoot implements Equatable
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

    abstract public function getId(): Uuid;
}
