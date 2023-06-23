<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

interface ProjectorPositionHandlerInterface
{
    public function getPosition(ProjectorInterface $projector): ?\DateTimeImmutable;

    public function storePosition(ProjectorInterface $projector, \DateTimeImmutable $position): void;

    public function isBroken(ProjectorInterface $projector): bool;

    public function markAsBroken(ProjectorInterface $projector): void;

    public function flush(): void;
}
