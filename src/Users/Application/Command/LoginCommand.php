<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Command;

use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final readonly class LoginCommand implements CommandInterface
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}
