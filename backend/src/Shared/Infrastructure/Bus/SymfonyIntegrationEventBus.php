<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\MessageBusInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Shared\Infrastructure\Event\IntegrationEvent;

final readonly class SymfonyIntegrationEventBus implements IntegrationEventBusInterface
{
    public function __construct(private MessageBusInterface $integrationEventBus)
    {
    }

    /**
     * @param DomainEventInterface[] $events
     */
    public function dispatch(array $events, int $version = null): void
    {
        foreach ($events as $event) {
            $integrationEvent = new IntegrationEvent($event, $version);

            $this->integrationEventBus->dispatch($integrationEvent);
        }
    }
}
