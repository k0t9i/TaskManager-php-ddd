<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\ValueObject\TaskId;

interface TaskFinderInterface
{
    public function find(TaskId $id): Task;
}
