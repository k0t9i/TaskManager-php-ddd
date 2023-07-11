<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;

final class ProjectParticipantProjection implements Hashable
{
    public function __construct(
        public string $userId,
        public string $projectId,
        public string $userEmail,
        public string $userFirstname,
        public string $userLastname,
        public int $tasksCount = 0
    ) {
    }

    public function getHash(): string
    {
        return $this->projectId;
    }
}
