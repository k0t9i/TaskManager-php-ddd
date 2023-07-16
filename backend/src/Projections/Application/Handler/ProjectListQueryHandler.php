<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\ProjectListQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class ProjectListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectListProjectionRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return ProjectListProjection[]
     */
    public function __invoke(ProjectListQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $criteria = new Criteria([
            new Operand('userId', OperatorEnum::Equal, $user->id),
        ]);

        return $this->repository->findAllByCriteria($criteria);
    }
}
