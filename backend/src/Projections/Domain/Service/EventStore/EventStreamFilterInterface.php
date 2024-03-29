<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface EventStreamFilterInterface
{
    public function isSuitable(DomainEventInterface $domainEvent): bool;
}
