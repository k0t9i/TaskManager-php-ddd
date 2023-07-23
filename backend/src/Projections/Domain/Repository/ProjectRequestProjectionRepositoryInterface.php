<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

/**
 * @method findAllByCriteria(Criteria $criteria): ProjectRequestProjection[]
 */
interface ProjectRequestProjectionRepositoryInterface extends PageableRepositoryInterface
{
    public function findById(string $id): ?ProjectRequestProjection;

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByUserId(string $id): array;

    public function save(ProjectRequestProjection $projection): void;
}
