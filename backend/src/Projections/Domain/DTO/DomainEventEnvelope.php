<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

final readonly class DomainEventEnvelope
{
    public function __construct(
        public DomainEventInterface $event,
        public ?int $version
    ) {
    }
}
