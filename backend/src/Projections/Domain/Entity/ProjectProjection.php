<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectProjection implements Hashable
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public string $description,
        public DateTime $finishDate,
        public string $ownerId,
        public int $status,
        public bool $isOwner = true
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