<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Entity;

use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Users\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Users\Domain\Event\UserWasCreatedDomainEvent;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

final class User extends AggregateRoot
{
    public function __construct(
        private readonly UserId $id,
        private readonly UserEmail $email,
        private UserProfile $profile
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

    public function changeProfile(UserProfile $profile): void
    {
        $this->profile = $profile;

        $this->registerEvent(new UserProfileWasChangedEvent(
            $this->id->value,
            $this->profile->firstname->value,
            $this->profile->lastname->value,
            $this->profile->password->value,
        ));
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getPassword(): UserPassword
    {
        return $this->profile->password;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->email->equals($this->email)
            && $other->profile->equals($this->profile);
    }
}
