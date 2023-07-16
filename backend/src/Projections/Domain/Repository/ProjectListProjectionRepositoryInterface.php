<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

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
    public function findAllOwnersProjects(): array;

    /**
     * @return ProjectListProjection[]
     */
    public function findAllWhereUserInvolved(string $userId): array;

    /**
     * @return ProjectListProjection[]
     */
    public function findAllByCriteria(Criteria $criteria): array;

    public function save(ProjectListProjection $projection): void;

    public function delete(ProjectListProjection $projection): void;
}
