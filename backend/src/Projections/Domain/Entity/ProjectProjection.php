<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\ProjectMemento;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectProjection implements Hashable
{
    public function __construct(
        private readonly string $id,
        public string $userId,
        private string $name,
        private string $description,
        private DateTime $finishDate,
        private string $ownerId,
        private int $status
    ) {
    }

    public static function hash(string $id, string $userId): string
    {
        return $id.$userId;
    }

    public function getHash(): string
    {
        return self::hash($this->id, $this->userId);
    }

    public function __clone()
    {
        $this->finishDate = clone $this->finishDate;
    }

    public function cloneForUser(string $userId): self
    {
        $newProjection = clone $this;
        $newProjection->userId = $userId;

        return $newProjection;
    }

    public static function create(
        string $id,
        string $userId,
        string $name,
        string $description,
        string $finishDate,
        string $ownerId,
        string $status,
    ): self {
        return new self(
            $id,
            $userId,
            $name,
            $description,
            new DateTime($finishDate),
            $ownerId,
            (int) $status,
        );
    }

    public function changeInformation(string $name, string $description, string $finishDate): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->finishDate = new DateTime($finishDate);
    }

    public function changeOwner(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function changeStatus(string $status): void
    {
        $this->status = (int) $status;
    }

    public function createMemento(): ProjectMemento
    {
        return new ProjectMemento(
            $this->id,
            $this->name,
            $this->description,
            $this->finishDate->getValue(),
            $this->status,
            $this->isOwner()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUserOwner(string $userId): bool
    {
        return $this->ownerId === $userId;
    }

    private function isOwner(): bool
    {
        return $this->isUserOwner($this->userId);
    }
}
