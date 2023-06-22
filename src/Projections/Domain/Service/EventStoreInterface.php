<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

interface EventStoreInterface
{
    public function getStream(\DateTimeImmutable $lastDatetime): EventStreamInterface;
}
