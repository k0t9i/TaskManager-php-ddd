<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

use TaskManager\Shared\Application\DTO\RequestCriteriaDTO;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaFromRequestBuilderInterface
{
    public function build(Criteria $criteria, RequestCriteriaDTO $dto): Criteria;
}
