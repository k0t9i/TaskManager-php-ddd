<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRegisterDTO
{
    public function __construct(
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/email'
            )]
        )]
        #[Assert\NotBlank]
        public string $email = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/firstname'
            )]
        )]
        #[Assert\NotBlank]
        public string $firstname = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/lastname'
            )]
        )]
        #[Assert\NotBlank]
        public string $lastname = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/password'
            )]
        )]
        #[Assert\NotBlank]
        public string $password = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/userModel/properties/password',
                description: 'User password repeating',
            )]
        )]
        #[Assert\NotBlank]
        public string $repeatPassword = '',
    ) {
    }
}
