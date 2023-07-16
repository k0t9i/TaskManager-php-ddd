<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Projections\Domain\Repository\ProjectParticipantProjectionRepositoryInterface;

final readonly class DoctrineProjectParticipantProjectionRepository implements ProjectParticipantProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByUserId(string $id): array
    {
        return $this->getRepository()->findBy([
            'userId' => $id,
        ]);
    }

    public function findByProjectAndUserId(string $projectId, string $userId): ?ProjectParticipantProjection
    {
        return $this->getRepository()->findOneBy([
            'projectId' => $projectId,
            'userId' => $userId,
        ]);
    }

    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByProjectId(string $id): array
    {
        return $this->getRepository()->findBy([
            'projectId' => $id,
        ]);
    }

    public function save(ProjectParticipantProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(ProjectParticipantProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(ProjectParticipantProjection::class);
    }
}
