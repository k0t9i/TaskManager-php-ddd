<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class ProjectListMemento
{
    public function __construct(
        public string $id,
        public string $name,
        public string $finishDate,
        public string $ownerId,
        public string $ownerFullName,
        public int $status,
        public int $tasksCount,
        public int $participantsCount,
        public int $pendingRequestsCount,
        public bool $isOwner,
        public bool $isInvolved,
        public ?int $lastRequestStatus
    ) {
    }
}
