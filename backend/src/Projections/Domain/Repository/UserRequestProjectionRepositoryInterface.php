<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\UserRequestProjection;

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
    public function findAllByUserId(string $id): array;

    public function save(UserRequestProjection $projection): void;
}
