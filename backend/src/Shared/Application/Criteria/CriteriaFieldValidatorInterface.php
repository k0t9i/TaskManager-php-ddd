<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Criteria;

use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaFieldValidatorInterface
{
    /**
     * @param class-string $class
     */
    public function validate(Criteria $criteria, string $class): void;
}
