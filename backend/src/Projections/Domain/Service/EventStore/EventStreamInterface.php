<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;

interface EventStreamInterface
{
    public function next(): ?DomainEventEnvelope;

    public function eventCount(): int;
}
