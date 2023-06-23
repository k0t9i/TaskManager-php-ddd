<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

interface ProjectionistInterface
{
    public function projectAll(): void;
}
