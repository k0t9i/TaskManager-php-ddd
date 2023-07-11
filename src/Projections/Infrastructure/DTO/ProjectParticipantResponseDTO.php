<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\ProjectParticipantProjection;

final readonly class ProjectParticipantResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'User ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $userId,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/email'
            )]
        )]
        public string $userEmail,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/firstname'
            )]
        )]
        public string $userFirstname,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/lastname'
            )]
        )]
        public string $userLastname,
        #[OA\Property(
            description: 'Count of participant tasks',
            type: 'int',
            example: 10
        )]
        public int $tasksCount
    ) {
    }

    /**
     * @param ProjectParticipantProjection[] $projections
     *
     * @return self[]
     */
    public static function createFromProjections(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self(
                $projection->userId,
                $projection->userEmail,
                $projection->userFirstname,
                $projection->userLastname,
                $projection->tasksCount
            );
        }

        return $result;
    }
}
