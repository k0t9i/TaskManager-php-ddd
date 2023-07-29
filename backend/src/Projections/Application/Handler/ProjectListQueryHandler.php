<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\ProjectListQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Application\Criteria\CriteriaFromQueryBuilderInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Application\Paginator\PaginatorInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;

final readonly class ProjectListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectListProjectionRepositoryInterface $repository,
        private CriteriaFromQueryBuilderInterface $criteriaBuilder,
        private CurrentUserExtractorInterface $userExtractor,
        private PaginatorInterface $paginator
    ) {
    }

    public function __invoke(ProjectListQuery $query): Pagination
    {
        $user = $this->userExtractor->extract();

        $criteria = new Criteria();

        $criteria->addOperand(new Operand('userId', OperatorEnum::Equal, $user->getId()))
            ->addOrder(new Order('finishDate'));

        $this->criteriaBuilder->build($criteria, $query->criteria);

        return $this->paginator->paginate($this->repository, $criteria);
    }
}
