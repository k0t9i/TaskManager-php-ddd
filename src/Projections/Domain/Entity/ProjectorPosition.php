<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

final class ProjectorPosition
{
    public function __construct(
        private readonly string $projectorName,
        private ?\DateTime $position = null,
        private bool $isBroken = false
    ) {
    }

    public function adjustPosition(?\DateTime $position): void
    {
        $this->position = $position;
    }

    public function markAsBroken(): void
    {
        $this->isBroken = true;
    }

    public function isBroken(): bool
    {
        return $this->isBroken;
    }

    public function getPosition(): ?\DateTimeImmutable
    {
        if (null === $this->position) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->position);
    }
}
