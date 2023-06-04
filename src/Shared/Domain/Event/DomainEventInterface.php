<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Event;

interface DomainEventInterface
{
    public static function getEventName(): string;

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static;

    public function toPrimitives(): array;
}
