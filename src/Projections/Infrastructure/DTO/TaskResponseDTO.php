<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\TaskProjection;

final readonly class TaskResponseDTO
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
                ref: '#components/schemas/taskModel/properties/brief'
            )]
        )]
        public string $brief,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/description'
            )]
        )]
        public string $description,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/finishDate'
            )]
        )]
        public string $startDate,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/startDate'
            )]
        )]
        public string $finishDate,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/status'
            )]
        )]
        public int $status
    ) {
    }

    public static function createFromProjection(TaskProjection $projection): self
    {
        return new self(
            $projection->id,
            $projection->name,
            $projection->brief,
            $projection->description,
            $projection->startDate->getValue(),
            $projection->finishDate->getValue(),
            $projection->status
        );
    }
}
