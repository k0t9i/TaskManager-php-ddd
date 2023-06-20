<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface DomainEventFactoryInterface
{
    /**
     * @return DomainEventInterface[]
     */
    public function create(
        string $eventName,
        string $aggregateId,
        array $body,
        string $performerId,
        string $occurredOn
    ): array;
}
