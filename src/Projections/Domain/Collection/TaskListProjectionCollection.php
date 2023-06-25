<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class TaskListProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return TaskListProjection::class;
    }
}
