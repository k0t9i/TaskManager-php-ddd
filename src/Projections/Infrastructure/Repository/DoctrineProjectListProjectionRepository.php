<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;

final readonly class DoctrineProjectListProjectionRepository implements ProjectListProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return ProjectListProjection[]
     */
    public function findAllById(string $id): array
    {
        return $this->getRepository()->findBy([
            'id' => $id,
        ]);
    }

    /**
     * @return ProjectListProjection[]
     */
    public function findAllByOwnerId(string $id): array
    {
        return $this->getRepository()->findBy([
            'ownerId' => $id,
        ]);
    }

    public function save(ProjectListProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(ProjectListProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(ProjectListProjection::class);
    }
}
