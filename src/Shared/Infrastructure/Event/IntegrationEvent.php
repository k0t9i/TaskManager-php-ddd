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
    private string $domainEventName;

    public function __construct(DomainEventInterface $domainEvent)
    {
        $this->aggregateId = $domainEvent->getAggregateId();
        $this->body = $domainEvent->toPrimitives();
        $this->occurredOn = $domainEvent->getOccurredOn();
        $this->domainEventName = $domainEvent::getEventName();
    }

    /**
     * @return string
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getOccurredOn(): string
    {
        return $this->occurredOn;
    }

    /**
     * @return string
     */
    public function getDomainEventName(): string
    {
        return $this->domainEventName;
    }
}
