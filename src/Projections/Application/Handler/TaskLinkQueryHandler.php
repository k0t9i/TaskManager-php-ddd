<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskLinkQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class TaskLinkQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskLinkProjectionRepositoryInterface $repository,
        private TaskProjectionRepositoryInterface $taskRepository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return TaskLinkProjection[]
     */
    public function __invoke(TaskLinkQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $task = $this->taskRepository->findById($query->taskId);
        if (null === $task) {
            throw new ObjectDoesNotExistException(sprintf('Task "%s" does not exist.', $query->taskId));
        }

        $project = $this->projectRepository->findByIdAndUserId($task->projectId, $user->id);
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $task->projectId));
        }

        return $this->repository->findAllByTaskId($query->taskId);
    }
}
