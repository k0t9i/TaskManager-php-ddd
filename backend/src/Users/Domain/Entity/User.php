<?php

declare(strict_types=1);

namespace TaskManager\Users\Domain\Entity;

use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Users\Domain\Event\UserWasCreatedEvent;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserLastname;
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

        $result->registerEvent(new UserWasCreatedEvent(
            $result->id->value,
            $result->email->value,
            $result->profile->firstname->value,
            $result->profile->lastname->value,
            $result->profile->password->value,
            $result->id->value
        ));

        return $result;
    }

    public function changeProfile(UserFirstname $firstname, UserLastname $lastname, ?UserPassword $password): void
    {
        $profile = new UserProfile(
            $firstname,
            $lastname,
            $password ?? $this->profile->password,
        );

        if (!$this->profile->equals($profile)) {
            $this->profile = $profile;

            $this->registerEvent(new UserProfileWasChangedEvent(
                $this->id->value,
                $this->profile->firstname->value,
                $this->profile->lastname->value,
                $this->profile->password->value,
                $this->id->value
            ));
        }
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
