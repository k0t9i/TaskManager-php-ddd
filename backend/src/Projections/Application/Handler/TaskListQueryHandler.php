<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskListQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class TaskListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskListProjectionRepositoryInterface $repository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return TaskListProjection[]
     */
    public function __invoke(TaskListQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $projectById = $this->projectRepository->findById($query->projectId);
        if (null === $projectById) {
            throw new ObjectDoesNotExistException(sprintf('Project "%s" does not exist.', $query->projectId));
        }

        $project = $this->projectRepository->findByIdAndUserId($query->projectId, $user->id);
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $query->projectId));
        }

        return $this->repository->findAllByProjectId($query->projectId);
    }
}
