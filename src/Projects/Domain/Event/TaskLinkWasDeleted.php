<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class TaskLinkWasDeleted extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $linkedTaskId,
        string $performerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $performerId, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'task.linkDeleted';
    }

    public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $performerId,
        string $occurredOn
    ): static {
        return new self($aggregateId, $body['linkedTaskId'], $performerId, $occurredOn);
    }

    public function toPrimitives(): array
    {
        return [
            'linkedTaskId' => $this->linkedTaskId,
        ];
    }
}
