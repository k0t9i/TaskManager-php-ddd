<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

interface ProjectParticipantProjectionRepositoryInterface
{
    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByUserId(string $id): array;

    public function findByProjectAndUserId(string $projectId, string $userId): ?ProjectParticipantProjection;

    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByCriteria(Criteria $criteria): array;

    public function save(ProjectParticipantProjection $projection): void;

    public function delete(ProjectParticipantProjection $projection): void;
}
