<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Projections\Domain\Repository\TaskLinkProjectionRepositoryInterface;

final readonly class DoctrineTaskLinkProjectionRepository implements TaskLinkProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByTaskId(string $id): array
    {
        return $this->getRepository()->findBy([
            'taskId' => $id,
        ]);
    }

    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByLinkedTaskId(string $id): array
    {
        return $this->getRepository()->findBy([
            'linkedTaskId' => $id,
        ]);
    }

    /**
     * @return TaskLinkProjection[]
     */
    public function findAllByTaskIdAndUserId(string $taskId, string $userId): array
    {
        return $this->getRepository()->findBy([
            'taskId' => $taskId,
            'userId' => $userId,
        ]);
    }

    public function save(TaskLinkProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(TaskLinkProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(TaskLinkProjection::class);
    }
}
