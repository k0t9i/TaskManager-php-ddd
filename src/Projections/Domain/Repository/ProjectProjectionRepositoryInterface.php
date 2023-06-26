<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectProjection;

interface ProjectProjectionRepositoryInterface
{
    /**
     * @return ProjectProjection[]
     */
    public function findAllById(string $id): array;

    public function findById(string $id): ?ProjectProjection;

    public function findByIdAndUserId(string $id, string $userId): ?ProjectProjection;

    public function save(ProjectProjection $projection): void;

    public function delete(ProjectProjection $projection): void;
}
