<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Event;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface IntegrationEventBusInterface
{
    /**
     * @param DomainEventInterface[] $events
     */
    public function dispatch(array $events, int $version = null): void;
}
