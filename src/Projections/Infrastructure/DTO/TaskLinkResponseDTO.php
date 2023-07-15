<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\TaskLinkProjection;

final readonly class TaskLinkResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'TaskId ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $taskId,
        #[OA\Property(
            description: 'Linked task ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $linkedTaskId,
        #[OA\Property(
            description: 'Linked task name',
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/name'
            )]
        )]
        public string $linkedTaskName,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/status'
            )]
        )]
        public int $linkedTaskStatus
    ) {
    }

    /**
     * @param TaskLinkProjection[] $projections
     *
     * @return self[]
     */
    public static function createFromProjections(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self(
                $projection->taskId,
                $projection->linkedTaskId,
                $projection->linkedTaskName,
                $projection->linkedTaskStatus
            );
        }

        return $result;
    }
}
