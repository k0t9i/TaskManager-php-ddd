<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\TaskLinkProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class TaskLinkProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return TaskLinkProjection::class;
    }
}
