<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\MessageBusInterface;
use TaskManager\Shared\Application\Bus\Event\DomainEventBusInterface;
use TaskManager\Shared\Domain\Event\DomainEventInterface;

final readonly class SymfonyDomainEventBus implements DomainEventBusInterface
{
    public function __construct(private MessageBusInterface $domainEventBus)
    {
    }

    public function dispatch(DomainEventInterface ...$events): void
    {
        foreach ($events as $event) {
            $this->domainEventBus->dispatch($event);
        }
    }
}
