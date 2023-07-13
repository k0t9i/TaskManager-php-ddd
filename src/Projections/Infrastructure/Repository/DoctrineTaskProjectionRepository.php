<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Projections\Domain\Repository\TaskProjectionRepositoryInterface;

final readonly class DoctrineTaskProjectionRepository implements TaskProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return TaskProjection[]
     */
    public function findAllByProjectId(string $id): array
    {
        return $this->getRepository()->findBy([
            'projectId' => $id,
        ]);
    }

    public function findById(string $id): ?TaskProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    public function save(TaskProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(TaskProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(TaskProjection::class);
    }
}
