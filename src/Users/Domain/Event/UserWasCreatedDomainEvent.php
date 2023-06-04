<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

final class UserWasCreatedDomainEvent extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $email,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $password,
        string $occurredOn = null
    ) {
        parent::__construct($id, $occurredOn);
    }

    public static function getEventName(): string
    {
        return 'user.created';
    }

    public static function fromPrimitives(string $aggregateId, array $body, string $occurredOn): static
    {
        return new self(
            $aggregateId,
            $body['email'],
            $body['firstname'],
            $body['lastname'],
            $body['password'],
            $occurredOn
        );
    }

    public function toPrimitives(): array
    {
        return [
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'password' => $this->password,
        ];
    }
}
