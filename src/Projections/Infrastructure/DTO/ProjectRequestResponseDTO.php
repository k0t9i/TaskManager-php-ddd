<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;

final readonly class ProjectRequestResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Request ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $id,
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
            oneOf: [new OA\Schema(
                ref: '#components/schemas/requestModel/properties/status'
            )]
        )]
        public int $status,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/requestModel/properties/changeDate'
            )]
        )]
        public string $changeDate
    ) {
    }

    /**
     * @param ProjectRequestProjection[] $projections
     *
     * @return self[]
     */
    public static function createFromProjections(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self(
                $projection->id,
                $projection->userId,
                $projection->userEmail,
                $projection->userFirstname,
                $projection->userLastname,
                $projection->status,
                $projection->changeDate->getValue()
            );
        }

        return $result;
    }
}
