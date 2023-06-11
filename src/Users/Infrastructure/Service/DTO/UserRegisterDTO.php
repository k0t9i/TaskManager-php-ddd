<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service\DTO;

final readonly class UserRegisterDTO
{
    public function __construct(
        public string $email = '',
        public string $firstname = '',
        public string $lastname = '',
        public string $password = '',
        public string $repeatPassword = '',
    ) {
    }
}
