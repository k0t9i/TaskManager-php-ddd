<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\DTO;

final readonly class QueryCriteriaDTO
{
    public function __construct(
        public array $filters,
        public array $orders,
        public ?int $offset,
        public ?int $limit
    ) {
    }
}
