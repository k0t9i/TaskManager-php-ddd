<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

// For development purposes only: app domains pretend to be microservices
final class SimulateMicroserviceEventStreamFilter implements EventStreamFilterInterface
{
    public function isSuitable(DomainEventInterface $domainEvent): bool
    {
        $eventDomain = explode('\\', $domainEvent::class)[1] ?? null;
        $projectionsDomain = explode('\\', self::class)[1] ?? null;

        if (null === $eventDomain || null === $projectionsDomain) {
            return false;
        }

        return $eventDomain === $projectionsDomain;
    }
}
