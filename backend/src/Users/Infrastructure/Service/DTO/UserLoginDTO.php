<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserLoginDTO
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
                ref: '#components/schemas/userModel/properties/password'
            )]
        )]
        #[Assert\NotBlank]
        public string $password = '',
    ) {
    }
}
