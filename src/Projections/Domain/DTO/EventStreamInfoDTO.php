<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

use TaskManager\Projections\Domain\Service\EventStore\EventStreamInterface;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final readonly class EventStreamInfoDTO
{
    public function __construct(
        public EventStreamInterface $stream,
        public ?DateTime $lastPosition
    ) {
    }
}
