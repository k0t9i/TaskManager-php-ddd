<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaFieldValidatorInterface
{
    /**
     * @param class-string $class
     */
    public function validate(Criteria $criteria, string $class): void;
}
