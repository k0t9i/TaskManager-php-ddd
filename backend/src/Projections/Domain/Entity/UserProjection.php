<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\UserMemento;

final class UserProjection
{
    public function __construct(
        private readonly string $id,
        private readonly string $email,
        private string $firstname,
        private string $lastname,
        private ?int $version
    ) {
    }

    public static function fullName(string $firstname, string $lastname): string
    {
        return $firstname.' '.$lastname;
    }

    public function getFullName(): string
    {
        return self::fullName($this->firstname, $this->lastname);
    }

    public static function create(
        string $id,
        string $email,
        string $firstname,
        string $lastname,
        ?int $version
    ): self {
        return new self(
            $id,
            $email,
            $firstname,
            $lastname,
            $version
        );
    }

    public function changeInformation(string $firstname, string $lastname, ?int $version): void
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->version = $version;
    }

    public function createMemento(): UserMemento
    {
        return new UserMemento(
            $this->id,
            $this->email,
            $this->firstname,
            $this->lastname,
            $this->version
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }
}
