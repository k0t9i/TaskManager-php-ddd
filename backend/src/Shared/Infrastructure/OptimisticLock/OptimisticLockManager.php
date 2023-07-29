<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\OptimisticLock;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLock;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLockManagerInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;

final readonly class OptimisticLockManager implements OptimisticLockManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    /**
     * @param class-string $aggregateRootClass
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\PessimisticLockException
     */
    public function lock(string $aggregateRootClass, int $expectedVersion): void
    {
        $lock = $this->entityManager->getRepository(OptimisticLock::class)->findOneBy([
            'aggregateRoot' => $aggregateRootClass,
        ]);
        if (null === $lock) {
            $lock = new OptimisticLock($aggregateRootClass);
        } else {
            $this->entityManager->lock($lock, LockMode::OPTIMISTIC, $expectedVersion);
        }

        $lock->uuid = $this->uuidGenerator->generate();

        $this->entityManager->persist($lock);
        $this->entityManager->flush();
    }
}
