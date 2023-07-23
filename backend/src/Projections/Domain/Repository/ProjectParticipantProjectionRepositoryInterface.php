<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;
use TaskManager\Shared\Domain\Criteria\Criteria;

/**
 * @method findAllByCriteria(Criteria $criteria): ProjectParticipantProjection[]
 */
interface ProjectParticipantProjectionRepositoryInterface extends PageableRepositoryInterface
{
    /**
     * @return ProjectParticipantProjection[]
     */
    public function findAllByUserId(string $id): array;

    public function findByProjectAndUserId(string $projectId, string $userId): ?ProjectParticipantProjection;

    public function save(ProjectParticipantProjection $projection): void;

    public function delete(ProjectParticipantProjection $projection): void;
}
