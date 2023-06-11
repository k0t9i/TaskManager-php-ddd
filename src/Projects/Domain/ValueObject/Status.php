<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;

abstract class Status implements Equatable
{
    abstract public function getScalar(): int;

    abstract public static function createFromScalar(int $status): static;

    abstract protected function getNextStatuses(): array;

    abstract public function allowsModification(): bool;

    public function canBeChangedTo(self $status): bool
    {
        return in_array(get_class($status), $this->getNextStatuses(), true);
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof static;
    }
}
