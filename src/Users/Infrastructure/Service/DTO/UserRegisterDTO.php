<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRegisterDTO
{
    public function __construct(
        #[OA\Property(
            description: 'User email',
            type: 'string',
            format: 'email',
            example: 'john.smith@local.dev'
        )]
        #[Assert\NotBlank]
        public string $email = '',
        #[OA\Property(
            description: 'User firstname',
            type: 'string',
            maxLength: 255,
            example: 'John'
        )]
        #[Assert\NotBlank]
        public string $firstname = '',
        #[OA\Property(
            description: 'User lastname',
            type: 'string',
            maxLength: 255,
            example: 'Smith'
        )]
        #[Assert\NotBlank]
        public string $lastname = '',
        #[OA\Property(
            description: 'User password',
            type: 'string',
            format: 'password'
        )]
        #[Assert\NotBlank]
        public string $password = '',
        #[OA\Property(
            description: 'User password repeating',
            type: 'string',
            format: 'password'
        )]
        #[Assert\NotBlank]
        public string $repeatPassword = '',
    ) {
    }
}
