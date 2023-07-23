<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Paginator;

use TaskManager\Projections\Domain\Repository\PageableRepositoryInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface PaginatorInterface
{
    public function paginate(PageableRepositoryInterface $repository, Criteria $criteria): Pagination;
}
