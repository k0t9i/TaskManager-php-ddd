<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

/**
 * @method findAllByCriteria(Criteria $criteria): TaskLinkProjection[]
 */
interface TaskLinkProjectionRepositoryInterface extends PageableRepositoryInterface
{
    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByLinkedTaskId(string $id): array;

    public function findByTaskAndLinkedTaskId(string $taskId, string $linkedTaskId): ?TaskLinkProjection;

    public function save(TaskLinkProjection $projection): void;

    public function delete(TaskLinkProjection $projection): void;
}
