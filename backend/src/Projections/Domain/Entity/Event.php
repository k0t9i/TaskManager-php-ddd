<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Shared\Domain\ValueObject\DateTime;
use TaskManager\Shared\Infrastructure\Service\DomainEventFactoryInterface;

final readonly class Event
{
    public function __construct(
        private string $id,
        private string $name,
        private string $aggregateId,
        private string $body,
        private string $performerId,
        private DateTime $occurredOn
    ) {
    }

    /**
     * @throws \Exception
     */
    public static function fromDomainEvent(string $id, DomainEventInterface $domainEvent): self
    {
        return new self(
            $id,
            $domainEvent::getEventName(),
            $domainEvent->getAggregateId(),
            json_encode($domainEvent->toPrimitives()),
            $domainEvent->getPerformerId(),
            new DateTime($domainEvent->getOccurredOn())
        );
    }

    /**
     * @return DomainEventInterface[]
     */
    public function createDomainEvents(DomainEventFactoryInterface $eventFactory): array
    {
        return $eventFactory->create(
            $this->name,
            $this->aggregateId,
            json_decode($this->body, true),
            $this->performerId,
            $this->occurredOn->getValue()
        );
    }
}
