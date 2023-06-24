<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final class ProjectionistResultDTO
{
    public function __construct(
        public string $projector,
        public int $eventCount,
        public bool $isBroken = false
    ) {
    }
}
