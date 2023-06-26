<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectListProjection;

interface ProjectListProjectionRepositoryInterface
{
    /**
     * @return ProjectListProjection[]
     */
    public function findAllById(string $id): array;

    /**
     * @return ProjectListProjection[]
     */
    public function findAllByOwnerId(string $id): array;

    /**
     * @return ProjectListProjection[]
     */
    public function findAllByUserId(string $id): array;

    public function save(ProjectListProjection $projection): void;

    public function delete(ProjectListProjection $projection): void;
}
