<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\TaskListQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Application\Criteria\CriteriaFromQueryBuilderInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Application\Paginator\PaginatorInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;

final readonly class TaskListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TaskListProjectionRepositoryInterface $repository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CriteriaFromQueryBuilderInterface $criteriaBuilder,
        private CurrentUserExtractorInterface $userExtractor,
        private PaginatorInterface $paginator
    ) {
    }

    public function __invoke(TaskListQuery $query): Pagination
    {
        $user = $this->userExtractor->extract();

        $projectById = $this->projectRepository->findById($query->projectId);
        if (null === $projectById) {
            throw new ObjectDoesNotExistException(sprintf('Project "%s" does not exist.', $query->projectId));
        }

        $project = $this->projectRepository->findByIdAndUserId($query->projectId, $user->getId());
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $query->projectId));
        }

        $criteria = new Criteria();

        $criteria->addOperand(new Operand('projectId', OperatorEnum::Equal, $query->projectId))
            ->addOrder(new Order('startDate'));

        $this->criteriaBuilder->build($criteria, $query->criteria);

        return $this->paginator->paginate($this->repository, $criteria);
    }
}
