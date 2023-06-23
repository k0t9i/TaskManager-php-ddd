<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class ProjectListProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return ProjectListProjection::class;
    }
}
