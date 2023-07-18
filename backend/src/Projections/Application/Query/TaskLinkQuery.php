<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Query;

use TaskManager\Shared\Application\Bus\Query\QueryInterface;
use TaskManager\Shared\Application\Criteria\QueryCriteriaDTO;

final readonly class TaskLinkQuery implements QueryInterface
{
    public function __construct(
        public string $taskId,
        public QueryCriteriaDTO $criteria
    ) {
    }
}
