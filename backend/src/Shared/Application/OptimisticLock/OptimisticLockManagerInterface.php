<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\OptimisticLock;

interface OptimisticLockManagerInterface
{
    /**
     * @param class-string $aggregateRootClass
     */
    public function lock(string $aggregateRootClass, int $expectedVersion): void;
}
