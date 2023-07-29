<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\TaskLinkMemento;
use TaskManager\Projections\Domain\Entity\TaskLinkProjection;

final readonly class TaskLinkResponseDTO
{
    #[OA\Property(
        description: 'TaskId ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $taskId;
    #[OA\Property(
        description: 'Linked task ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $linkedTaskId;
    #[OA\Property(
        description: 'Linked task name',
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/name'
        )]
    )]
    public string $linkedTaskName;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/status'
        )]
    )]
    public int $linkedTaskStatus;

    public function __construct(TaskLinkMemento $memento)
    {
        $this->taskId = $memento->taskId;
        $this->linkedTaskId = $memento->linkedTaskId;
        $this->linkedTaskName = $memento->linkedTaskName;
        $this->linkedTaskStatus = $memento->linkedTaskStatus;
    }

    /**
     * @param TaskLinkProjection[] $projections
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
