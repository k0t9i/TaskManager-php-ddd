<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class ProjectParticipantMemento
{
    public function __construct(
        public string $userId,
        public string $userEmail,
        public string $userFirstname,
        public string $userLastname,
        public int $tasksCount
    ) {
    }
}
