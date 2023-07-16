<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Exception\TaskDoesNotExistException;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskId;

final readonly class TaskFinder implements TaskFinderInterface
{
    public function __construct(private TaskRepositoryInterface $repository)
    {
    }

    public function find(TaskId $id): Task
    {
        $task = $this->repository->findById($id);
        if (null === $task) {
            throw new TaskDoesNotExistException($id->value);
        }

        return $task;
    }
}
