<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;

final readonly class ProjectListResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Project ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $id,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/name'
            )]
        )]
        public string $name,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/finishDate'
            )]
        )]
        public string $finishDate,
        #[OA\Property(
            description: 'Project owner ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $ownerId,
        #[OA\Property(
            description: 'Project owner email',
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/email'
            )]
        )]
        public string $ownerEmail,
        #[OA\Property(
            description: 'Project owner firstname',
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/firstname'
            )]
        )]
        public string $ownerFirstname,
        #[OA\Property(
            description: 'Project owner lastname',
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/lastname'
            )]
        )]
        public string $ownerLastname,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/status'
            )]
        )]
        public int $status,
        #[OA\Property(
            description: 'Count of project tasks',
            type: 'int',
            example: 10
        )]
        public int $tasksCount,
        #[OA\Property(
            description: 'Count of project participants',
            type: 'int',
            example: 10
        )]
        public int $participantsCount,
        #[OA\Property(
            description: 'Count of project requests in pending status',
            type: 'int',
            example: 10
        )]
        public int $pendingRequestsCount,
        #[OA\Property(
            description: 'Is current user project owner?',
            type: 'bool',
            example: true
        )]
        public bool $isOwner
    ) {
    }

    /**
     * @param ProjectListProjection[] $projections
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
                $projection->finishDate->getValue(),
                $projection->ownerId,
                $projection->ownerEmail,
                $projection->ownerFirstname,
                $projection->ownerLastname,
                $projection->status,
                $projection->tasksCount,
                $projection->participantsCount,
                $projection->pendingRequestsCount,
                $projection->isOwner
            );
        }

        return $result;
    }
}
