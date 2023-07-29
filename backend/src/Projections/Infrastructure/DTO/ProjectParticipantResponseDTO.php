<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\ProjectParticipantMemento;
use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;

final readonly class ProjectParticipantResponseDTO
{
    #[OA\Property(
        description: 'User ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $userId;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/email'
        )]
    )]
    public string $userEmail;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/firstname'
        )]
    )]
    public string $userFirstname;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/lastname'
        )]
    )]
    public string $userLastname;
    #[OA\Property(
        description: 'Count of participant tasks',
        type: 'int',
        example: 10
    )]
    public int $tasksCount;

    public function __construct(ProjectParticipantMemento $memento)
    {
        $this->userId = $memento->userId;
        $this->userEmail = $memento->userEmail;
        $this->userFirstname = $memento->userFirstname;
        $this->userLastname = $memento->userLastname;
        $this->tasksCount = $memento->tasksCount;
    }

    /**
     * @param ProjectParticipantProjection[] $projections
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
