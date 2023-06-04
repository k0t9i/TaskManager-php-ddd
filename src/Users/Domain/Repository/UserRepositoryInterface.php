<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Repository;

use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;

    public function findByEmail(UserEmail $email): ?User;

    public function save(User $user): void;
}
