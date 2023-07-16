<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Repository;

use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\ValueObject\ProjectId;

interface ProjectRepositoryInterface
{
    public function findById(ProjectId $id): ?Project;

    public function save(Project $project): void;
}
