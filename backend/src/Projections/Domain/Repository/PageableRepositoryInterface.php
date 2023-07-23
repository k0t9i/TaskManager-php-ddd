<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Shared\Domain\Criteria\Criteria;

interface PageableRepositoryInterface
{
    public function findAllByCriteria(Criteria $criteria): array;

    public function findCountByCriteria(Criteria $criteria): int;
}
