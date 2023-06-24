<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

use TaskManager\Projections\Domain\Service\EventStore\EventStreamInterface;

final readonly class EventStreamInfoDTO
{
    public function __construct(
        public EventStreamInterface $stream,
        public ?\DateTimeImmutable $lastPosition
    ) {
    }
}
