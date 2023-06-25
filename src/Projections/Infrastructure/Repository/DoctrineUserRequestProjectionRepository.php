<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;
use TaskManager\Projections\Domain\Repository\UserRequestProjectionRepositoryInterface;

final readonly class DoctrineUserRequestProjectionRepository implements UserRequestProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(string $id): ?UserRequestProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return UserRequestProjection[]
     */
    public function findAllByProjectId(string $id): array
    {
        return $this->getRepository()->findBy([
            'projectId' => $id,
        ]);
    }

    public function save(UserRequestProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(UserRequestProjection::class);
    }
}
