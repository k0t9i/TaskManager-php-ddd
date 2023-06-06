<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service\DTO;

final readonly class UserLoginDTO
{
    public function __construct(
        public string $email = "",
        public string $password = "",
    ) {
    }
}
