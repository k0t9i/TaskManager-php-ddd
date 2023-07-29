<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\UserRequestMemento;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;

final readonly class UserRequestResponseDTO
{
    #[OA\Property(
        description: 'Request ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $id;
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
    #[OA\Property(
        description: 'Project ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $projectId;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/projectModel/properties/name'
        )]
    )]
    public string $projectName;

    public function __construct(UserRequestMemento $memento)
    {
        $this->id = $memento->id;
        $this->status = $memento->status;
        $this->changeDate = $memento->changeDate;
        $this->projectId = $memento->projectId;
        $this->projectName = $memento->projectName;
    }

    /**
     * @param UserRequestProjection[] $projections
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
