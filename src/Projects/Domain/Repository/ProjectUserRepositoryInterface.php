<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Repository;

use TaskManager\Projects\Domain\Entity\ProjectUser;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;

interface ProjectUserRepositoryInterface
{
    public function findById(ProjectUserId $id): ?ProjectUser;

    public function save(ProjectUser $user): void;
}
