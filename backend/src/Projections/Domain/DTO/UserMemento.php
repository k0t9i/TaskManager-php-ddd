<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\DTO;

final readonly class UserMemento
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstname,
        public string $lastname,
        public ?int $version
    ) {
    }
}
