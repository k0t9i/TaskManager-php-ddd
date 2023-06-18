<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projects\Domain\Entity\ProjectUser;
use TaskManager\Projects\Domain\Repository\ProjectUserRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;

final readonly class DoctrineProjectUserRepository implements ProjectUserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(ProjectUserId $id): ?ProjectUser
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    public function save(ProjectUser $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(ProjectUser::class);
    }
}
