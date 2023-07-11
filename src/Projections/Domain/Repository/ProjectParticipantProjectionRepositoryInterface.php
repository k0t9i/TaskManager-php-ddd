<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;

interface ProjectParticipantProjectionRepositoryInterface
{
    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByUserId(string $id): array;

    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByProjectId(string $id): array;

    public function save(ProjectParticipantProjection $projection): void;

    public function delete(ProjectParticipantProjection $projection): void;
}
