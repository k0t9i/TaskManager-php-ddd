<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Collection;

use TaskManager\Shared\Domain\Hashable;

interface ManagedCollectionInterface
{
    public function addOrUpdateElement(Hashable $element): void;

    public function removeElement(Hashable $element): void;

    public function remove(string $key): void;

    public function get(string $key): Hashable;

    public function elementExists(Hashable $element): bool;

    public function exists(string $key): bool;

    /**
     * @return array<array-key, Hashable>
     */
    public function getItems(): array;

    /**
     * @return array<array-key, Hashable>
     */
    public function getRemovedItems(): array;

    public function flush(): void;

    public function findFirst(callable $callback = null): ?Hashable;
}
