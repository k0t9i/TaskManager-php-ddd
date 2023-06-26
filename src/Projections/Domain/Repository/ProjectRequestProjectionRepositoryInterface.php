<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;

interface ProjectRequestProjectionRepositoryInterface
{
    public function findById(string $id): ?ProjectRequestProjection;

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByUserId(string $id): array;

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByProjectId(string $id): array;

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByProjectIdAndOwnerId(string $projectId, string $userId): array;

    public function save(ProjectRequestProjection $projection): void;
}
