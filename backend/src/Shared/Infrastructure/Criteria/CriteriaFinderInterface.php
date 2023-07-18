<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\ORM\EntityRepository;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface CriteriaFinderInterface
{
    public function findAllByCriteria(EntityRepository $repository, Criteria $criteria): array;

    public function findCountByCriteria(EntityRepository $repository, Criteria $criteria): int;

    public function findByCriteria(EntityRepository $repository, Criteria $criteria): mixed;
}
