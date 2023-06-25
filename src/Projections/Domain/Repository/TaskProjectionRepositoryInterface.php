<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\TaskProjection;

interface TaskProjectionRepositoryInterface
{
    /**
     * @return TaskProjection[]
     */
    public function findAllById(string $id): array;

    /**
     * @return TaskProjection[]
     */
    public function findAllByProjectId(string $id): array;

    public function findById(string $id): ?TaskProjection;

    public function save(TaskProjection $projection): void;

    public function delete(TaskProjection $projection): void;
}
