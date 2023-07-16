<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectRequestProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $userEmail,
        public string $userFirstname,
        public string $userLastname,
        public int $status,
        public DateTime $changeDate,
        public string $projectId
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }
}
