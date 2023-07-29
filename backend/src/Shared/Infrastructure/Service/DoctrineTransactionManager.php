<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Doctrine\ORM\EntityManagerInterface;
use TaskManager\Shared\Domain\Service\TransactionManagerInterface;

final readonly class DoctrineTransactionManager implements TransactionManagerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \Exception
     */
    public function withTransaction(callable $callback): void
    {
        $this->entityManager->beginTransaction();

        try {
            call_user_func($callback);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
