<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface EventStreamFactoryInterface
{
    /**
     * @param DomainEventInterface[] $events
     */
    public function createStream(array $events): EventStreamInterface;
}
