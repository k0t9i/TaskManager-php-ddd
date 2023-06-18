<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class ProjectWasCreatedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $finishDate,
        public readonly string $status,
        public readonly string $ownerId,
        string $performerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $performerId, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'project.created';
    }

    public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $performerId,
        string $occurredOn
    ): static {
        return new self(
            $aggregateId,
            $body['name'],
            $body['description'],
            $body['finishDate'],
            $body['status'],
            $body['ownerId'],
            $performerId,
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'finishDate' => $this->finishDate,
            'status' => $this->status,
            'ownerId' => $this->ownerId,
        ];
    }
}
