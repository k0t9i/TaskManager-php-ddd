<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\UserRequestQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Projections\Domain\Repository\UserRequestProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Application\Criteria\CriteriaFromQueryBuilderInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;

final readonly class UserRequestQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRequestProjectionRepositoryInterface $repository,
        private CriteriaFromQueryBuilderInterface $criteriaBuilder,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return UserRequestProjection[]
     */
    public function __invoke(UserRequestQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $criteria = new Criteria();

        $criteria->addOperand(new Operand('userId', OperatorEnum::Equal, $user->id))
            ->addOrder(new Order('changeDate'));

        $this->criteriaBuilder->build($criteria, $query->criteria);

        return $this->repository->findAllByCriteria($criteria);
    }
}
