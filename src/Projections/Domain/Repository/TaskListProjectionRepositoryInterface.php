<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\TaskListProjection;

interface TaskListProjectionRepositoryInterface
{
    /**
     * @return TaskListProjection[]
     */
    public function findAllById(string $id): array;

    /**
     * @return TaskListProjection[]
     */
    public function findAllByOwnerId(string $id): array;

    /**
     * @return TaskListProjection[]
     */
    public function findAllByProjectId(string $id): array;

    public function save(TaskListProjection $projection): void;

    public function delete(TaskListProjection $projection): void;
}
