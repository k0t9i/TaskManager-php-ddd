<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\ProjectRequestMemento;
use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;

final readonly class ProjectRequestResponseDTO
{
    #[OA\Property(
        description: 'Request ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $id;
    #[OA\Property(
        description: 'User ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $userId;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/fullName'
        )]
    )]
    public string $userFullName;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/requestModel/properties/status'
        )]
    )]
    public int $status;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/requestModel/properties/changeDate'
        )]
    )]
    public string $changeDate;

    public function __construct(ProjectRequestMemento $memento)
    {
        $this->id = $memento->id;
        $this->userId = $memento->userId;
        $this->userFullName = $memento->userFullName;
        $this->status = $memento->status;
        $this->changeDate = $memento->changeDate;
    }

    /**
     * @param ProjectRequestProjection[] $projections
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
