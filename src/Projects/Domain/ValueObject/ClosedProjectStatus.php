<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

final class ClosedProjectStatus extends ProjectStatus
{
    public function allowsModification(): bool
    {
        return false;
    }

    protected function getNextStatuses(): array
    {
        return [ActiveProjectStatus::class];
    }
}
