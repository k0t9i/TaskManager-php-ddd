<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Collection;

use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class ProjectParticipantProjectionCollection extends ManagedCollection
{
    protected function supportClass(): string
    {
        return ProjectParticipantProjection::class;
    }
}
