<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

/**
 * @method findAllByCriteria(Criteria $criteria): ProjectListProjection[]
 */
interface ProjectListProjectionRepositoryInterface extends PageableRepositoryInterface
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

    public function save(ProjectListProjection $projection): void;

    public function delete(ProjectListProjection $projection): void;
}
