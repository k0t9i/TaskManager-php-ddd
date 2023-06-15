<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class ProjectTaskFinishDateWasChangedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $taskId,
        public readonly string $finishDate,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'projectTask.finishDateChanged';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self(
            $aggregateId,
            $body['taskId'],
            $body['finishDate'],
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'taskId' => $this->taskId,
            'finishDate' => $this->finishDate,
        ];
    }
}
