<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class TaskInformationWasChangedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $name,
        public readonly string $brief,
        public readonly string $description,
        public readonly string $startDate,
        public readonly string $finishDate,
        string $performerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $performerId, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'task.informationChanged';
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
            $body['brief'],
            $body['description'],
            $body['startDate'],
            $body['finishDate'],
            $performerId,
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'name' => $this->name,
            'brief' => $this->brief,
            'description' => $this->description,
            'startDate' => $this->startDate,
            'finishDate' => $this->finishDate,
        ];
    }
}
