<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Entity;

use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Users\Domain\Event\UserWasCreatedDomainEvent;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserProfile;

final class User extends AggregateRoot
{
    public function __construct(
        private readonly UserId $id,
        private readonly UserEmail $email,
        private readonly UserProfile $profile
    ) {
    }

    public static function create(UserId $id, UserEmail $email, UserProfile $profile): self
    {
        $result = new self($id, $email, $profile);

        $result->registerEvent(new UserWasCreatedDomainEvent(
            $result->id->value,
            $result->email->value,
            $result->profile->firstname->value,
            $result->profile->lastname->value,
            $result->profile->password->value
        ));

        return $result;
    }
}
