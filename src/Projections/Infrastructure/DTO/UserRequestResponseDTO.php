<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\UserRequestProjection;

final readonly class UserRequestResponseDTO
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
        public string $changeDate,
        #[OA\Property(
            description: 'Project ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $projectId,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/projectModel/properties/name'
            )]
        )]
        public string $projectName
    ) {
    }

    /**
     * @param UserRequestProjection[] $projections
     *
     * @return self[]
     */
    public static function createFromProjections(array $projections): array
    {
        $result = [];

        foreach ($projections as $projection) {
            $result[] = new self(
                $projection->id,
                $projection->status,
                $projection->changeDate->getValue(),
                $projection->projectId,
                $projection->projectName
            );
        }

        return $result;
    }
}
