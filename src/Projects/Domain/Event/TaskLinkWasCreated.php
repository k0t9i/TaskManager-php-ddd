<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class TaskLinkWasCreated extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $linkedTaskId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'task.linkCreated';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self($aggregateId, $body['linkedTaskId'], $occurredOn);
    }

    public function toPrimitives(): array
    {
        return [
            'linkedTaskId' => $this->linkedTaskId,
        ];
    }
}
