<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\TaskProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class TaskProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return TaskProjection::class;
    }
}
