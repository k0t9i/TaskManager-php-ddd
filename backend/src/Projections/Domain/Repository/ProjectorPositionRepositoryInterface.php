<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectorPosition;

interface ProjectorPositionRepositoryInterface
{
    public function findByProjectorName(string $name): ?ProjectorPosition;

    public function save(ProjectorPosition $position): void;
}
