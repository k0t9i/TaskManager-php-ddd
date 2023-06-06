<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class ProjectUserWasCreatedEvent extends DomainEvent
{
    public static function getEventName(): string
    {
        return 'user.created';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self(
            $aggregateId,
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [];
    }
}
