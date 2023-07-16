<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

final class UserProjection
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstname,
        public string $lastname
    ) {
    }
}
