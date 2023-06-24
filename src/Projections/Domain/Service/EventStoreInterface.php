<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Projections\Domain\DTO\EventStreamInfoDTO;

interface EventStoreInterface
{
    public function getStreamInfo(?\DateTimeImmutable $lastDatetime): EventStreamInfoDTO;
}
