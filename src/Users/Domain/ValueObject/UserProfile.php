<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;

final readonly class UserProfile implements Equatable
{
    public function __construct(
        public UserFirstname $firstname,
        public UserLastname  $lastname,
        public UserPassword  $password
    ) {
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->firstname->equals($this->firstname)
            && $other->lastname->equals($this->lastname)
            && $other->password->equals($this->password);
    }
}
