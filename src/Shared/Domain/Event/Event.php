<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Event;

abstract class Event
{
    public string $occurredOn;

    public function __construct(
        public string $aggregateId,
        string        $occurredOn = null
    ) {
        $this->occurredOn = $occurredOn ?: (new \DateTime())->format('Y-m-d\TH:i:s.uP');
    }

    abstract public static function getEventName(): string;

    abstract public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static;

    abstract public function toPrimitives(): array;
}
