<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class TaskWasCreatedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $projectId,
        public readonly string $name,
        public readonly string $brief,
        public readonly string $description,
        public readonly string $startDate,
        public readonly string $finishDate,
        public readonly string $status,
        public readonly string $ownerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'task.created';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self(
            $aggregateId,
            $body['projectId'],
            $body['name'],
            $body['brief'],
            $body['description'],
            $body['startDate'],
            $body['finishDate'],
            $body['status'],
            $body['ownerId'],
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'projectId' => $this->projectId,
            'name' => $this->name,
            'brief' => $this->brief,
            'description' => $this->description,
            'startDate' => $this->startDate,
            'finishDate' => $this->finishDate,
            'status' => $this->status,
            'ownerId' => $this->ownerId,
        ];
    }
}
