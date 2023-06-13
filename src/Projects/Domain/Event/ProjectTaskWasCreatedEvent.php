<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class ProjectTaskWasCreatedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $taskId,
        public readonly string $ownerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'projectTask.created';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self(
            $aggregateId,
            $body['taskId'],
            $body['ownerId'],
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'taskId' => $this->taskId,
            'ownerId' => $this->ownerId,
        ];
    }
}
