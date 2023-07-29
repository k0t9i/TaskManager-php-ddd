<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\TaskListMemento;
use TaskManager\Projections\Domain\Entity\TaskListProjection;

final readonly class TaskListResponseDTO
{
    #[OA\Property(
        description: 'Task ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $id;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/name'
        )]
    )]
    public string $name;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/startDate'
        )]
    )]
    public string $startDate;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/finishDate'
        )]
    )]
    public string $finishDate;
    #[OA\Property(
        description: 'Task owner ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $ownerId;
    #[OA\Property(
        description: 'Task owner full name',
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/fullName'
        )]
    )]
    public string $ownerFullName;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/status'
        )]
    )]
    public int $status;
    #[OA\Property(
        description: 'Count of task links',
        type: 'int',
        example: 10
    )]
    public int $linksCount;

    public function __construct(TaskListMemento $memento)
    {
        $this->id = $memento->id;
        $this->name = $memento->name;
        $this->startDate = $memento->startDate;
        $this->finishDate = $memento->finishDate;
        $this->ownerId = $memento->ownerId;
        $this->ownerFullName = $memento->ownerFullName;
        $this->status = $memento->status;
        $this->linksCount = $memento->linksCount;
    }

    /**
     * @param TaskListProjection[] $projections
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
