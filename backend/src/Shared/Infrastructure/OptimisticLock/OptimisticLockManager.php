<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\OptimisticLock;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLock;
use TaskManager\Shared\Application\OptimisticLock\OptimisticLockManagerInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;

final readonly class OptimisticLockManager implements OptimisticLockManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\PessimisticLockException
     */
    public function lock(AggregateRoot $aggregateRoot, int $expectedVersion): void
    {
        $lock = $this->entityManager->getRepository(OptimisticLock::class)->findOneBy([
            'aggregateRoot' => $aggregateRoot::class,
            'aggregateId' => $aggregateRoot->getId()->value
        ]);
        if (null === $lock) {
            $lock = new OptimisticLock($aggregateRoot::class, $aggregateRoot->getId()->value);
        } else {
            $this->entityManager->lock($lock, LockMode::OPTIMISTIC, $expectedVersion);
        }

        $lock->uuid = $this->uuidGenerator->generate();

        $this->entityManager->persist($lock);
        $this->entityManager->flush();
    }
}
