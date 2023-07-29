<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class TaskQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskProjectionRepositoryInterface $repository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    public function __invoke(TaskQuery $query): TaskProjection
    {
        $user = $this->userExtractor->extract();

        $task = $this->repository->findById($query->id);
        if (null === $task) {
            throw new ObjectDoesNotExistException(sprintf('Task "%s" does not exist.', $query->id));
        }

        $project = $this->projectRepository->findByIdAndUserId($task->getProjectId(), $user->getId());
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $task->getProjectId()));
        }

        return $task;
    }
}
