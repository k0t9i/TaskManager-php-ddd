<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\TaskMemento;
use TaskManager\Projections\Domain\Entity\TaskProjection;

final readonly class TaskResponseDTO
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
            ref: '#components/schemas/taskModel/properties/brief'
        )]
    )]
    public string $brief;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/description'
        )]
    )]
    public string $description;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/finishDate'
        )]
    )]
    public string $startDate;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/startDate'
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
        oneOf: [new OA\Schema(
            ref: '#components/schemas/taskModel/properties/status'
        )]
    )]
    public int $status;

    public function __construct(TaskMemento $memento)
    {
        $this->id = $memento->id;
        $this->name = $memento->name;
        $this->brief = $memento->brief;
        $this->description = $memento->description;
        $this->startDate = $memento->startDate;
        $this->finishDate = $memento->finishDate;
        $this->ownerId = $memento->ownerId;
        $this->status = $memento->status;
    }

    public static function create(TaskProjection $projection): self
    {
        return new self($projection->createMemento());
    }
}
