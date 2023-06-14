<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Projects\Domain\Exception\UserIsNotTaskOwnerException;
use TaskManager\Shared\Domain\Equatable;

final readonly class TaskOwner implements Equatable
{
    public function __construct(
        public ProjectUserId $id
    ) {
    }

    public function ensureUserIsOwner(ProjectUserId $userId): void
    {
        if (!$this->userIsOwner($userId)) {
            throw new UserIsNotTaskOwnerException($userId->value);
        }
    }

    public function userIsOwner(ProjectUserId $userId): bool
    {
        return $this->id->equals($userId);
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id);
    }
}
