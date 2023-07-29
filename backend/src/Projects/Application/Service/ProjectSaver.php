<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLockManagerInterface;
use TaskManager\Shared\Domain\Service\TransactionManagerInterface;

final readonly class ProjectSaver implements ProjectSaverInterface
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private OptimisticLockManagerInterface $lockManager,
        private TransactionManagerInterface $transactionManager
    ) {
    }

    public function save(Project $project, int $expectedVersion): int
    {
        $newVersion = 0;

        $this->transactionManager->withTransaction(function () use ($project, $expectedVersion, &$newVersion) {
            $newVersion = $this->lockManager->lock($project, $expectedVersion);
            $this->repository->save($project);
        });

        return $newVersion;
    }
}
