<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;

final readonly class DoctrineTaskListProjectionRepository implements TaskListProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(string $id): ?TaskListProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return TaskListProjection[]
     */
    public function findAllByOwnerId(string $id): array
    {
        return $this->getRepository()->findBy([
            'ownerId' => $id,
        ]);
    }

    /**
     * @return TaskListProjection[]
     */
    public function findAllByProjectId(string $id): array
    {
        return $this->getRepository()->findBy([
            'projectId' => $id,
        ]);
    }

    public function save(TaskListProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(TaskListProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(TaskListProjection::class);
    }
}
