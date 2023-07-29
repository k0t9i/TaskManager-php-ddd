<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\DTO;

use OpenApi\Attributes as OA;
use TaskManager\Projections\Domain\DTO\UserMemento;
use TaskManager\Projections\Domain\Entity\UserProjection;

final readonly class UserResponseDTO
{
    #[OA\Property(
        description: 'User ID',
        oneOf: [new OA\Schema(
            ref: '#/components/schemas/objectId/properties/id'
        )]
    )]
    public string $id;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/email'
        )]
    )]
    public string $email;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/firstname'
        )]
    )]
    public string $firstname;
    #[OA\Property(
        oneOf: [new OA\Schema(
            ref: '#components/schemas/userModel/properties/lastname'
        )]
    )]
    public string $lastname;

    public function __construct(UserMemento $memento)
    {
        $this->id = $memento->id;
        $this->email = $memento->email;
        $this->firstname = $memento->firstname;
        $this->lastname = $memento->lastname;
    }

    public static function create(UserProjection $projection): self
    {
        return new self($projection->createMemento());
    }
}
