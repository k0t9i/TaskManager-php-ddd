<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\TaskLinkProjection;

interface TaskLinkProjectionRepositoryInterface
{
    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByTaskId(string $id): array;

    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByLinkedTaskId(string $id): array;

    public function save(TaskLinkProjection $projection): void;

    public function delete(TaskLinkProjection $projection): void;
}
