<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

final readonly class Order
{
    public function __construct(
        public string $property,
        public bool $isAsc = true
    ) {
    }
}
