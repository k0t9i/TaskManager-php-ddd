<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\OptimisticLock;

final class OptimisticLock
{
    public int $version = 1;

    public string $uuid = '';

    /**
     * @param class-string $aggregateRoot
     */
    public function __construct(
        public readonly string $aggregateRoot,
        public readonly string $aggregateId
    ) {
    }
}
