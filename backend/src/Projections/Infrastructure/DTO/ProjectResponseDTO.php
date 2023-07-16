<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\ProjectProjection;

final readonly class ProjectResponseDTO
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
                ref: '#components/schemas/projectModel/properties/description'
            )]
        )]
        public string $description,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/finishDate'
            )]
        )]
        public string $finishDate,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/status'
            )]
        )]
        public int $status,
        #[OA\Property(
            description: 'Is current user project owner?',
            type: 'bool',
            example: true
        )]
        public bool $isOwner
    ) {
    }

    public static function createFromProjection(ProjectProjection $projection): self
    {
        return new self(
            $projection->id,
            $projection->name,
            $projection->description,
            $projection->finishDate->getValue(),
            $projection->status,
            $projection->isOwner
        );
    }
}
