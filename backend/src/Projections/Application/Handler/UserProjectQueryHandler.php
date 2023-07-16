<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\UserProjectQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class UserProjectQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectListProjectionRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return ProjectListProjection[]
     */
    public function __invoke(UserProjectQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $criteria = new \TaskManager\Shared\Domain\Criteria\Criteria([
            new Operand('userId', OperatorEnum::Equal, $user->id),
            new Operand('isInvolved', OperatorEnum::Equal, true),
        ]);

        return $this->repository->findAllByCriteria($criteria);
    }
}
