<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\ValueObject;

final readonly class UserProfile
{
    public function __construct(
        public UserFirstname $firstname,
        public UserLastname  $lastname,
        public UserPassword  $password
    ) {
    }
}
