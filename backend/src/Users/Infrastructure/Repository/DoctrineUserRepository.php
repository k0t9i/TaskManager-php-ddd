<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Shared\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(UserId $id): ?User
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    public function findByEmail(UserEmail $email): ?User
    {
        return $this->getRepository()->findOneBy([
            'email' => $email,
        ]);
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(User::class);
    }
}
