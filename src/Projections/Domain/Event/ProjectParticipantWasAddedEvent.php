<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class ProjectParticipantWasAddedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $participantId,
        string $performerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $performerId, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'project.participantAdded';
    }

    public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $performerId,
        string $occurredOn
    ): static {
        return new self($aggregateId, $body['participantId'], $performerId, $occurredOn);
    }

    public function toPrimitives(): array
    {
        return [
            'participantId' => $this->participantId,
        ];
    }
}
