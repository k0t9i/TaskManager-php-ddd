<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\EventStore;

use TaskManager\Projections\Domain\DTO\EventStreamInfoDTO;
use TaskManager\Shared\Domain\ValueObject\DateTime;

interface EventStoreInterface
{
    public function getStreamInfo(?DateTime $lastDatetime): EventStreamInfoDTO;
}
