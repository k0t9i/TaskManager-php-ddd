<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\ValueObject\UserId;

final readonly class ProjectOwner implements Equatable
{
    public function __construct(
        public UserId $id
    ) {
    }

    public function ensureUserIsOwner(UserId $userId): void
    {
        if (!$this->userIsOwner($userId)) {
            throw new UserIsNotProjectOwnerException($userId->value);
        }
    }

    public function ensureUserIsNotOwner(UserId $userId): void
    {
        if ($this->userIsOwner($userId)) {
            throw new UserIsAlreadyProjectOwnerException($userId->value);
        }
    }

    public function userIsOwner(UserId $userId): bool
    {
        return $this->id->equals($userId);
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id);
    }
}
