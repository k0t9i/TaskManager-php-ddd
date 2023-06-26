<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\ProjectQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Exception\InsufficientPermissionsException;
use TaskManager\Projections\Domain\Exception\ObjectDoesNotExistException;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class ProjectQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ProjectProjectionRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor
    ) {
    }

    public function __invoke(ProjectQuery $query): ProjectProjection
    {
        $user = $this->userExtractor->extract();

        $projectBydId = $this->repository->findById($query->id);
        if (null === $projectBydId) {
            throw new ObjectDoesNotExistException(sprintf('Project "%s" does not exist.', $query->id));
        }

        $project = $this->repository->findByIdAndUserId($query->id, $user->id);
        if (null === $project) {
            throw new InsufficientPermissionsException(sprintf('Insufficient permissions to view the project "%s".', $query->id));
        }

        return $project;
    }
}
