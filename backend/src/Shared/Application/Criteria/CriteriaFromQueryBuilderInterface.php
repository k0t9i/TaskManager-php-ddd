<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Criteria;

use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaFromQueryBuilderInterface
{
    public function build(Criteria $criteria, QueryCriteriaDTO $dto): Criteria;
}
