<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Event;

use TaskManager\Shared\Domain\ValueObject\DateTime;

abstract class DomainEvent implements DomainEventInterface
{
    public string $occurredOn;

    public function __construct(
        public string $aggregateId,
        string        $occurredOn = null
    ) {
        $this->occurredOn = $occurredOn ?: (new DateTime())->getValue();
    }

    abstract public static function getEventName(): string;

    abstract public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static;

    abstract public function toPrimitives(): array;
}
