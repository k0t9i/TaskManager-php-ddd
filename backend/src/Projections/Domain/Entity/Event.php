<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Shared\Domain\Service\DomainEventFactoryInterface;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final readonly class Event
{
    public function __construct(
        private string $id,
        private string $name,
        private string $aggregateId,
        private string $body,
        private string $performerId,
        private DateTime $occurredOn,
        private ?int $version
    ) {
    }

    /**
     * @throws \Exception
     */
    public static function fromDomainEvent(string $id, DomainEventInterface $domainEvent, ?int $version): self
    {
        return new self(
            $id,
            $domainEvent::getEventName(),
            $domainEvent->getAggregateId(),
            json_encode($domainEvent->toPrimitives()),
            $domainEvent->getPerformerId(),
            new DateTime($domainEvent->getOccurredOn()),
            $version
        );
    }

    /**
     * @return DomainEventEnvelope[]
     */
    public function createEventEnvelope(DomainEventFactoryInterface $eventFactory): array
    {
        $domainEvents = $eventFactory->create(
            $this->name,
            $this->aggregateId,
            json_decode($this->body, true),
            $this->performerId,
            $this->occurredOn->getValue()
        );

        $result = [];

        foreach ($domainEvents as $domainEvent) {
            $result[] = new DomainEventEnvelope($domainEvent, $this->version);
        }

        return $result;
    }
}
