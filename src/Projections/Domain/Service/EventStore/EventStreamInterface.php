<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface EventStreamInterface
{
    public function next(): ?DomainEventInterface;

    public function eventCount(): int;
}