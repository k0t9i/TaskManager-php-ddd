<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Paginator;

use TaskManager\Projections\Domain\Repository\PageableRepositoryInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;

final class Paginator implements PaginatorInterface
{
    public function paginate(PageableRepositoryInterface $repository, Criteria $criteria): Pagination
    {
        return new Pagination(
            $repository->findAllByCriteria($criteria),
            $repository->findCountByCriteria($criteria),
            $criteria->getOffset(),
            $criteria->getLimit()
        );
    }
}
