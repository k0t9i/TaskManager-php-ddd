<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaToDoctrineCriteriaConverterInterface
{
    public function convert(Criteria $criteria): DoctrineCriteria;
}
