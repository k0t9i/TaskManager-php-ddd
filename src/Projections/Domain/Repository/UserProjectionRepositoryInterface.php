<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\UserProjection;

interface UserProjectionRepositoryInterface
{
    public function findById(string $id): ?UserProjection;

    /**
     * @return UserProjection[]
     */
    public function findAll(): array;

    public function save(UserProjection $projection): void;
}
