<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskLinkQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Application\Criteria\CriteriaFromQueryBuilderInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Application\Paginator\PaginatorInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;

final readonly class TaskLinkQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskLinkProjectionRepositoryInterface $repository,
        private TaskProjectionRepositoryInterface $taskRepository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CriteriaFromQueryBuilderInterface $criteriaBuilder,
        private CurrentUserExtractorInterface $userExtractor,
        private PaginatorInterface $paginator
    ) {
    }

    public function __invoke(TaskLinkQuery $query): Pagination
    {
        $user = $this->userExtractor->extract();

        $task = $this->taskRepository->findById($query->taskId);
        if (null === $task) {
            throw new ObjectDoesNotExistException(sprintf('Task "%s" does not exist.', $query->taskId));
        }

        $project = $this->projectRepository->findByIdAndUserId($task->getProjectId(), $user->getId());
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $task->getProjectId()));
        }

        $criteria = new Criteria();

        $criteria->addOperand(new Operand('taskId', OperatorEnum::Equal, $query->taskId))
            ->addOrder(new Order('linkedTaskName'));

        $this->criteriaBuilder->build($criteria, $query->criteria);

        return $this->paginator->paginate($this->repository, $criteria);
    }
}
