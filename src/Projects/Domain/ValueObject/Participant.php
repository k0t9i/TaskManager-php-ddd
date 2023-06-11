<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;

final readonly class Participant implements Equatable
{
    public function __construct(
        public ProjectId     $projectId,
        public ProjectUserId $userId
    ) {
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->projectId->equals($this->projectId)
            && $other->userId->equals($this->userId);
    }
}
