<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use TaskManager\Shared\Domain\Hashable;

interface ManagedCollectionManagerInterface
{
    /**
     * @param array<array-key, Hashable> $items
     */
    public function load(object $owner, string $propertyName, array $items): void;

    public function flush(object $owner, string $propertyName): void;
}
