<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\MessageBusInterface;
use TaskManager\Shared\Application\Bus\Event\EventBusInterface;
use TaskManager\Shared\Domain\Event\DomainEvent;

final readonly class SymfonyEventBus implements EventBusInterface
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
