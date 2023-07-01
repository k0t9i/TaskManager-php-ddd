<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;

final readonly class DoctrineUserProjectionRepository implements UserProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(string $id): ?UserProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return UserProjection[]
     */
    public function findAll(): array
    {
        return $this->getRepository()->findAll();
    }

    public function save(UserProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(UserProjection::class);
    }
}
