<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Repository;

use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\ValueObject\TaskId;

interface TaskRepositoryInterface
{
    public function findById(TaskId $id): ?Task;

    public function save(Task $task): void;
}
