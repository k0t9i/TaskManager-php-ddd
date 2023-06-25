<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

final class ProjectRequestProjection
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $userEmail,
        public string $userFirstname,
        public string $userLastname,
        public string $status,
        public \DateTime $changeDate,
        public string $projectId,
        public string $ownerId
    ) {
    }
}
