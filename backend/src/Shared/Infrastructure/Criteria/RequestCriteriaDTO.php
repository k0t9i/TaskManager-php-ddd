<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

final readonly class RequestCriteriaDTO
{
    public function __construct(
        public array $filters = [],
        public array $orders = []
    ) {
    }
}
