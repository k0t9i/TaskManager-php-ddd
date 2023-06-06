<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use phpDocumentor\Reflection\Types\Self_;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Shared\Domain\Equatable;

final readonly class ProjectOwner implements Equatable
{
    public function __construct(
        public ProjectUserId $userId
    ) {
    }

    public function ensureUserIsOwner(ProjectUserId $userId): void
    {
        if (!$this->userIsOwner($userId)) {
            throw new UserIsNotProjectOwnerException($userId->value);
        }
    }

    public function ensureUserIsNotOwner(ProjectUserId $userId): void
    {
        if ($this->userIsOwner($userId)) {
            throw new UserIsAlreadyProjectOwnerException($userId->value);
        }
    }

    public function userIsOwner(ProjectUserId $userId): bool
    {
        return $this->userId->equals($userId);
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->userId->equals($this->userId);
    }
}
