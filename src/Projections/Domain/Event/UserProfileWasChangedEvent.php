<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class UserProfileWasChangedEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $password,
        string $performerId,
        string $occurredOn = null
    ) {
        parent::__construct($id, $performerId, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'user.profileChanged';
    }

    public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $performerId,
        string $occurredOn
    ): static {
        return new self(
            $aggregateId,
            $body['firstname'],
            $body['lastname'],
            $body['password'],
            $performerId,
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'password' => $this->password,
        ];
    }
}
