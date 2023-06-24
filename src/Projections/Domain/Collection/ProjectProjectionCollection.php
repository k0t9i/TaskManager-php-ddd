<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\ProjectProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class ProjectProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return ProjectProjection::class;
    }
}
