<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Symfony\DTO;

final readonly class UserProfileUpdateDTO
{
    public function __construct(
        public string $firstname = "",
        public string $lastname = "",
        public string $password = "",
        public string $repeatPassword = ""
    ) {
    }
}
