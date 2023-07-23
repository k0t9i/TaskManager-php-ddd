<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

/**
 * @method findAllByCriteria(Criteria $criteria): UserRequestProjection[]
 */
interface UserRequestProjectionRepositoryInterface extends PageableRepositoryInterface
{
    public function findById(string $id): ?UserRequestProjection;

    /**
     * @return UserRequestProjection[]
     */
    public function findAllByProjectId(string $id): array;

    public function save(UserRequestProjection $projection): void;
}
