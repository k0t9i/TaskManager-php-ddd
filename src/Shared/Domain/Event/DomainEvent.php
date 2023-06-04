<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Event;

use TaskManager\Shared\Domain\ValueObject\DateTime;

abstract class DomainEvent implements DomainEventInterface
{
    private readonly string $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
        string $occurredOn = null
    ) {
        $this->occurredOn = $occurredOn ?: (new DateTime())->getValue();
    }

    /**
     * @return string
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function getOccurredOn(): string
    {
        return $this->occurredOn;
    }

    abstract public static function getEventName(): string;

    abstract public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static;

    abstract public function toPrimitives(): array;
}
