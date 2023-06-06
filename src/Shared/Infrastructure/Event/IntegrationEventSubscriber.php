<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Event;

use TaskManager\Shared\Application\Bus\Event\DomainEventBusInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventSubscriberInterface;
use TaskManager\Shared\Domain\Event\DomainEvent;
use TaskManager\Shared\Infrastructure\Service\DomainEventMapperInterface;

final readonly class IntegrationEventSubscriber implements IntegrationEventSubscriberInterface
{
    public function __construct(
        private DomainEventMapperInterface $mapper,
        private DomainEventBusInterface $eventBus
    ) {
    }

    public function __invoke(IntegrationEvent $event): void
    {
        $map = $this->mapper->getEventMap();

        /** @var DomainEvent[] $domainEventClasses */
        $domainEventClasses = $map[$event->getDomainEventName()] ?? [];

        foreach ($domainEventClasses as $domainEventClass) {
            $domainEvent = $domainEventClass::fromPrimitives(
                $event->getAggregateId(),
                $event->getBody(),
                $event->getOccurredOn()
            );

            $this->eventBus->dispatch($domainEvent);
        }
    }
}
