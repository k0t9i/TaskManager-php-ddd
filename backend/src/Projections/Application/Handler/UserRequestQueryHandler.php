<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\UserRequestQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Projections\Domain\Repository\UserRequestProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class UserRequestQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRequestProjectionRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return UserRequestProjection[]
     */
    public function __invoke(UserRequestQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $criteria = new Criteria([
            new Operand('userId', OperatorEnum::Equal, $user->id),
        ]);

        return $this->repository->findAllByCriteria($criteria);
    }
}
