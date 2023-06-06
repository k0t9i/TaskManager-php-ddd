<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Command;

use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final readonly class UpdateProfileCommand implements CommandInterface
{
    public function __construct(
        public ?string $firstname,
        public ?string $lastname,
        public ?string $password,
        public ?string $repeatPassword
    ) {
    }
}
