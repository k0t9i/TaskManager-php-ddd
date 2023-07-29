<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLockManagerInterface;
use TaskManager\Shared\Domain\Service\TransactionManagerInterface;

final readonly class TaskSaver implements TaskSaverInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private OptimisticLockManagerInterface $lockManager,
        private TransactionManagerInterface $transactionManager
    ) {
    }

    public function save(Task $task, int $expectedVersion): int
    {
        $newVersion = 0;

        $this->transactionManager->withTransaction(function () use ($task, $expectedVersion, &$newVersion) {
            $newVersion = $this->lockManager->lock($task, $expectedVersion);
            $this->repository->save($task);
        });

        return $newVersion;
    }
}
