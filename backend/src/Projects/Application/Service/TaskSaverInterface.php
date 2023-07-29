<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Task;

interface TaskSaverInterface
{
    public function save(Task $task, int $expectedVersion): int;
}
