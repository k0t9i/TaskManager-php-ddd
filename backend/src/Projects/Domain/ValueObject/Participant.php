<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\UserId;

final readonly class Participant implements Equatable, Hashable
{
    public function __construct(
        public ProjectId $projectId,
        public UserId $userId
    ) {
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->projectId->equals($this->projectId)
            && $other->userId->equals($this->userId);
    }

    public function getHash(): string
    {
        return $this->userId->value;
    }
}
