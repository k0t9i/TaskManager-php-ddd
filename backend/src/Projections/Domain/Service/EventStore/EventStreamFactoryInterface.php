<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;

interface EventStreamFactoryInterface
{
    /**
     * @param DomainEventEnvelope[] $envelopes
     */
    public function createStream(array $envelopes): EventStreamInterface;
}
