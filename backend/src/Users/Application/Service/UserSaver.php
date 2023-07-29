<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Service;

use TaskManager\Shared\Application\OptimisticLock\OptimisticLockManagerInterface;
use TaskManager\Shared\Domain\Service\TransactionManagerInterface;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;

final readonly class UserSaver implements UserSaverInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private OptimisticLockManagerInterface $lockManager,
        private TransactionManagerInterface $transactionManager
    ) {
    }

    public function save(User $user, int $expectedVersion): int
    {
        $newVersion = 0;

        $this->transactionManager->withTransaction(function () use ($user, $expectedVersion, &$newVersion) {
            $newVersion = $this->lockManager->lock($user, $expectedVersion);
            $this->repository->save($user);
        });

        return $newVersion;
    }
}
