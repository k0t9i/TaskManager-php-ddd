<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectListProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public DateTime $finishDate,
        public string $ownerId,
        public string $ownerFullName,
        public int $status,
        public bool $isOwner,
        public bool $isInvolved,
        public int $tasksCount = 0,
        public int $participantsCount = 0,
        public int $pendingRequestsCount = 0,
        public ?int $lastRequestStatus = null
    ) {
    }

    public static function hash(string $id, string $userId): string
    {
        return $id.$userId;
    }

    public function getHash(): string
    {
        return self::hash($this->id, $this->userId);
    }

    public function __clone()
    {
        $this->finishDate = clone $this->finishDate;
    }
}
