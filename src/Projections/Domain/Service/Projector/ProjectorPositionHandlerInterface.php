<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Shared\Domain\ValueObject\DateTime;

interface ProjectorPositionHandlerInterface
{
    public function getPosition(ProjectorInterface $projector): ?DateTime;

    public function storePosition(ProjectorInterface $projector, DateTime $position): void;

    public function isBroken(ProjectorInterface $projector): bool;

    public function markAsBroken(ProjectorInterface $projector): void;

    public function flushPosition(ProjectorInterface $projector): void;
}
