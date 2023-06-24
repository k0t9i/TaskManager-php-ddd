<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

interface ProjectionistInterface
{
    public function projectAll(): void;
}
