<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

final class ActiveProjectStatus extends ProjectStatus
{
    public function allowsModification(): bool
    {
        return true;
    }

    protected function getNextStatuses(): array
    {
        return [ClosedProjectStatus::class];
    }
}
