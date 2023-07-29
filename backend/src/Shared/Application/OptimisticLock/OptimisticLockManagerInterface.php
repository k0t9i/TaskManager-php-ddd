<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\OptimisticLock;

use TaskManager\Shared\Domain\Aggregate\AggregateRoot;

interface OptimisticLockManagerInterface
{
    public function lock(AggregateRoot $aggregateRoot, int $expectedVersion): int;
}
