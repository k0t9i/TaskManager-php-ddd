<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use TaskManager\Shared\Application\Criteria\QueryCriteriaDTO;

interface QueryCriteriaFromRequestConverterInterface
{
    public function convert(RequestCriteriaDTO $dto): QueryCriteriaDTO;
}
