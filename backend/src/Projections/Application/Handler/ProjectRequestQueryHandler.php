<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\ProjectRequestQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\ProjectRequestProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class ProjectRequestQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectRequestProjectionRepositoryInterface $repository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return ProjectRequestProjection[]
     */
    public function __invoke(ProjectRequestQuery $query): array
    {
        $user = $this->userExtractor->extract();

        $project = $this->projectRepository->findById($query->projectId);
        if (null === $project) {
            throw new ObjectDoesNotExistException(sprintf('Project "%s" does not exist.', $query->projectId));
        }

        if ($project->ownerId !== $user->id) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $query->projectId));
        }

        $criteria = new Criteria([
            new Operand('projectId', OperatorEnum::Equal, $query->projectId),
        ]);

        return $this->repository->findAllByCriteria($criteria);
    }
}
