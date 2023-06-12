<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Collection;

use TaskManager\Shared\Domain\Hashable;

abstract class ManagedCollection implements ManagedCollectionInterface
{
    /**
     * @var array<array-key, Hashable>
     */
    private array $items = [];

    /**
     * @var array<array-key, Hashable>
     */
    private array $snapshot = [];

    /**
     * @param array<array-key, Hashable> $items
     */
    public function __construct(array $items = [])
    {
        $this->ensureItemsAreValidType($items);
        $this->indexItems($items);
        $this->takeSnapshot();
    }

    public function addOrUpdateElement(Hashable $element): void
    {
        $this->items[$element->getHash()] = $element;
    }

    public function removeElement(Hashable $element): void
    {
        $this->remove($element->getHash());
    }

    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }

    public function elementExists(Hashable $element): bool
    {
        return $this->exists($element->getHash());
    }

    public function exists(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key): Hashable
    {
        if (!$this->exists($key)) {
            throw new \LogicException(sprintf('Element with key "%s" does not exist.', $key));
        }

        return $this->items[$key];
    }

    /**
     * @return array<array-key, Hashable> $items
     */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    /**
     * @return array<array-key, Hashable> $items
     */
    public function getRemovedItems(): array
    {
        return array_values(array_diff_key($this->snapshot, $this->items));
    }

    public function flush(): void
    {
        $this->takeSnapshot();
    }

    private function takeSnapshot(): void
    {
        $this->snapshot = $this->items;
    }

    /**
     * @param array<array-key, Hashable> $items
     */
    private function indexItems(array $items): void
    {
        foreach ($items as $item) {
            $this->items[$item->getHash()] = $item;
        }
    }

    private function ensureItemsAreValidType(array $items): void
    {
        foreach ($items as $item) {
            if (!($item instanceof Hashable) || !is_a($item, $this->supportClass(), true)) {
                throw new \LogicException('Invalid type '.gettype($item));
            }
        }
    }

    /**
     * @return class-string
     */
    abstract protected function supportClass(): string;
}
