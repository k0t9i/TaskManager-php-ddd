<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface UserRequestProjectionRepositoryInterface
{
    public function findById(string $id): ?UserRequestProjection;

    /**
     * @return UserRequestProjection[]
     */
    public function findAllByProjectId(string $id): array;

    /**
     * @return UserRequestProjection[]
     */
    public function findAllByCriteria(Criteria $criteria): array;

    public function save(UserRequestProjection $projection): void;
}
