<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Project;

interface ProjectSaverInterface
{
    public function save(Project $project, int $expectedVersion): int;
}
