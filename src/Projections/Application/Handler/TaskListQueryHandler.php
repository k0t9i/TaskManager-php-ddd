<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskListQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class TaskListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskListProjectionRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return TaskListProjection[]
     */
    public function __invoke(TaskListQuery $query): array
    {
        $user = $this->userExtractor->extract();

        return $this->repository->findAllByProjectIdAndUserId($query->projectId, $user->id);
    }
}
