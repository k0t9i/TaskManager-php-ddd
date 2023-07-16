<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Event;

use TaskManager\Shared\Domain\ValueObject\DateTime;

abstract class DomainEvent implements DomainEventInterface
{
    private readonly string $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
        private readonly string $performerId,
        string $occurredOn = null
    ) {
        $this->occurredOn = $occurredOn ?: (new DateTime())->getValue();
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getOccurredOn(): string
    {
        return $this->occurredOn;
    }

    public function getPerformerId(): string
    {
        return $this->performerId;
    }
}
