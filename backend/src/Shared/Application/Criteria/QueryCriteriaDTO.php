<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Criteria;

final readonly class QueryCriteriaDTO
{
    /**
     * @param QueryCriteriaFilterDTO[] $filters
     */
    public function __construct(
        public array $filters,
        public array $orders,
        public ?int $offset,
        public ?int $limit
    ) {
    }
}
