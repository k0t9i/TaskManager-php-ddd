<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\Entity\UserProjection;

final readonly class UserResponseDTO
{
    public function __construct(
        #[OA\Property(
            description: 'User ID',
            oneOf: [new OA\Schema(
                ref: '#/components/schemas/objectId/properties/id'
            )]
        )]
        public string $id,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/email'
            )]
        )]
        public string $email,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/firstname'
            )]
        )]
        public string $firstname,
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/lastname'
            )]
        )]
        public string $lastname
    ) {
    }

    public static function createFromProjection(UserProjection $projection): self
    {
        return new self(
            $projection->id,
            $projection->email,
            $projection->firstname,
            $projection->lastname
        );
    }
}
