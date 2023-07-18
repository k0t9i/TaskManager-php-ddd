<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Query;

use TaskManager\Shared\Application\Bus\Query\QueryInterface;
use TaskManager\Shared\Application\DTO\QueryCriteriaDTO;

final readonly class ProjectListQuery implements QueryInterface
{
    public function __construct(
        public QueryCriteriaDTO $criteria
    ) {
    }
}
