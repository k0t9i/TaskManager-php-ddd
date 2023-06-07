<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\ValueObject\ProjectId;

interface ProjectFinderInterface
{
    public function find(ProjectId $id): Project;
}
