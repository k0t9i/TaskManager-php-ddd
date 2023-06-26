<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Projections\Domain\Repository\ProjectProjectionRepositoryInterface;

final readonly class DoctrineProjectProjectionRepository implements ProjectProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return ProjectProjection[]
     */
    public function findAllById(string $id): array
    {
        return $this->getRepository()->findBy([
            'id' => $id,
        ]);
    }

    public function findById(string $id): ?ProjectProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    public function findByIdAndUserId(string $id, string $userId): ?ProjectProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
            'userId' => $userId,
        ]);
    }

    public function save(ProjectProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(ProjectProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(ProjectProjection::class);
    }
}
