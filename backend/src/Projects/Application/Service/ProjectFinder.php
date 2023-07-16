<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Exception\ProjectDoesNotExistException;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;

final readonly class ProjectFinder implements ProjectFinderInterface
{
    public function __construct(private ProjectRepositoryInterface $repository)
    {
    }

    public function find(ProjectId $id): Project
    {
        $project = $this->repository->findById($id);
        if (null === $project) {
            throw new ProjectDoesNotExistException($id->value);
        }

        return $project;
    }
}
