<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\ProjectListMemento;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;

final readonly class ProjectListResponseDTO
{
    #[OA\Property(
        description: 'Project ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $id;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/projectModel/properties/name'
        )]
    )]
    public string $name;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/projectModel/properties/finishDate'
        )]
    )]
    public string $finishDate;
    #[OA\Property(
        description: 'Project owner ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $ownerId;
    #[OA\Property(
        description: 'Project owner full name',
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/fullName'
        )]
    )]
    public string $ownerFullName;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/projectModel/properties/status'
        )]
    )]
    public int $status;
    #[OA\Property(
        description: 'Count of project tasks',
        type: 'int',
        example: 10
    )]
    public int $tasksCount;
    #[OA\Property(
        description: 'Count of project participants',
        type: 'int',
        example: 10
    )]
    public int $participantsCount;
    #[OA\Property(
        description: 'Count of project requests in pending status',
        type: 'int',
        example: 10
    )]
    public int $pendingRequestsCount;
    #[OA\Property(
        description: 'Is current user project owner?',
        type: 'bool',
        example: true
    )]
    public bool $isOwner;
    #[OA\Property(
        description: 'Is current user involved in the project?',
        type: 'bool',
        example: true
    )]
    public bool $isInvolved;
    #[OA\Property(
        description: 'Last project request status of current user',
        oneOf: [new OA\Schema(
            ref: '#components/schemas/requestModel/properties/status'
        )]
    )]
    public ?int $lastRequestStatus;

    public function __construct(ProjectListMemento $memento)
    {
        $this->id = $memento->id;
        $this->name = $memento->name;
        $this->finishDate = $memento->finishDate;
        $this->ownerId = $memento->ownerId;
        $this->ownerFullName = $memento->ownerFullName;
        $this->status = $memento->status;
        $this->tasksCount = $memento->tasksCount;
        $this->participantsCount = $memento->participantsCount;
        $this->pendingRequestsCount = $memento->pendingRequestsCount;
        $this->isOwner = $memento->isOwner;
        $this->isInvolved = $memento->isInvolved;
        $this->lastRequestStatus = $memento->lastRequestStatus;
    }

    /**
     * @param ProjectListProjection[] $projections
     *
     * @return self[]
     */
    public static function createList(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self($projection->createMemento());
        }

        return $result;
    }
}
