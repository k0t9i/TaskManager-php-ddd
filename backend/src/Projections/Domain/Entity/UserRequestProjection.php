<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class UserRequestProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public int $status,
        public DateTime $changeDate,
        public string $projectId,
        public string $projectName
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }
}