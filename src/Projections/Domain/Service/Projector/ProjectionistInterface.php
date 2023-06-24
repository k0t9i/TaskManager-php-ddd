<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\DTO\ProjectionistResultDTO;

interface ProjectionistInterface
{
    /**
     * @return ProjectionistResultDTO[]
     */
    public function projectAll(): array;
}
