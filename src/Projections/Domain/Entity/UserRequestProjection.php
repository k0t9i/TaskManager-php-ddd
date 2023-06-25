<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

final class UserRequestProjection
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $status,
        public \DateTime $changeDate,
        public string $projectId,
        public string $projectName
    ) {
    }
}
