<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\UserProjectQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

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

        return $this->repository->findAllWhereUserInvolved($user->id);
    }
}
