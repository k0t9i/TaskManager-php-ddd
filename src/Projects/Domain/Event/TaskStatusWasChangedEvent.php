<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class TaskStatusWasChangedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $status,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'task.statusChanged';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self($aggregateId, $body['status'], $occurredOn);
    }

    public function toPrimitives(): array
    {
        return [
            'status' => $this->status,
        ];
    }
}
