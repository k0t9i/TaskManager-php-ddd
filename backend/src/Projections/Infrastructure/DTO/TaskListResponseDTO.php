<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\TaskListProjection;

final readonly class TaskListResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Task ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $id,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/name'
            )]
        )]
        public string $name,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/startDate'
            )]
        )]
        public string $startDate,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/finishDate'
            )]
        )]
        public string $finishDate,
        #[OA\Property(
            description: 'Task owner ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $ownerId,
        #[OA\Property(
            description: 'Task owner full name',
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/fullName'
            )]
        )]
        public string $ownerFullName,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/status'
            )]
        )]
        public int $status,
        #[OA\Property(
            description: 'Count of task links',
            type: 'int',
            example: 10
        )]
        public int $linksCount
    ) {
    }

    /**
     * @param TaskListProjection[] $projections
     *
     * @return self[]
     */
    public static function createFromProjections(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self(
                $projection->id,
                $projection->name,
                $projection->startDate->getValue(),
                $projection->finishDate->getValue(),
                $projection->ownerId,
                $projection->ownerFullName,
                $projection->status,
                $projection->linksCount
            );
        }

        return $result;
    }
}
