<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Event;

use TaskManager\Shared\Application\Bus\Event\IntegrationEventInterface;
use TaskManager\Shared\Domain\Event\DomainEventInterface;

final readonly class IntegrationEvent implements IntegrationEventInterface
{
    private string $aggregateId;
    private array $body;
    private string $occurredOn;
    private string $performerId;
    private string $domainEventName;
    private ?int $version;

    public function __construct(DomainEventInterface $domainEvent, ?int $version)
    {
        $this->aggregateId = $domainEvent->getAggregateId();
        $this->body = $domainEvent->toPrimitives();
        $this->occurredOn = $domainEvent->getOccurredOn();
        $this->performerId = $domainEvent->getPerformerId();
        $this->domainEventName = $domainEvent::getEventName();
        $this->version = $version;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getPerformerId(): string
    {
        return $this->performerId;
    }

    public function getOccurredOn(): string
    {
        return $this->occurredOn;
    }

    public function getDomainEventName(): string
    {
        return $this->domainEventName;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }
}
