<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\ProjectParticipantQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectParticipantProjectionRepositoryInterface;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class ProjectParticipantQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectParticipantProjectionRepositoryInterface $repository,
        private ProjectProjectionRepositoryInterface $projectRepository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    /**
     * @return ProjectParticipantProjection[]
     */
    public function __invoke(ProjectParticipantQuery $query): array
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

        $criteria = new Criteria([
            new Operand('projectId', OperatorEnum::Equal, $query->projectId),
        ]);

        return $this->repository->findAllByCriteria($criteria);
    }
}
