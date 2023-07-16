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

    public function dispatch(DomainEventInterface ...$events): void
    {
        foreach ($events as $event) {
            $integrationEvent = new IntegrationEvent($event);

            $this->integrationEventBus->dispatch($integrationEvent);
        }
    }
}
