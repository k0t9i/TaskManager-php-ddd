<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\ProjectListMemento;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectListProjection implements Hashable
{
    private int $tasksCount = 0;

    private int $participantsCount = 0;

    private int $pendingRequestsCount = 0;

    private ?int $lastRequestStatus = null;

    public function __construct(
        private readonly string $id,
        private string $userId,
        private string $name,
        private DateTime $finishDate,
        private string $ownerId,
        private string $ownerFullName,
        private int $status,
        private bool $isInvolved
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

    public static function create(
        string $id,
        string $userId,
        string $name,
        string $finishDate,
        string $ownerId,
        string $ownerFullName,
        string $status,
    ): self {
        return new self(
            $id,
            $userId,
            $name,
            new DateTime($finishDate),
            $ownerId,
            $ownerFullName,
            (int) $status,
            $userId === $ownerId,
            $userId === $ownerId
        );
    }

    public function cloneForUser(string $userId): self
    {
        $newProjection = clone $this;
        $newProjection->userId = $userId;
        $newProjection->isInvolved = false;
        $newProjection->lastRequestStatus = null;

        return $newProjection;
    }

    public function changeInformation(string $name, string $finishDate): void
    {
        $this->name = $name;
        $this->finishDate = new DateTime($finishDate);
    }

    public function changeOwner(string $ownerId, string $ownerFullName): void
    {
        $this->ownerId = $ownerId;
        $this->ownerFullName = $ownerFullName;
        if ($this->isOwner()) {
            $this->isInvolved = true;
            $this->lastRequestStatus = null;
        }
    }

    public function changeOwnerFullName(string $fullName): void
    {
        $this->ownerFullName = $fullName;
    }

    public function changeStatus(string $status): void
    {
        $this->status = (int) $status;
    }

    public function addTask(): void
    {
        ++$this->tasksCount;
    }

    public function createRequest(string $userId, string $status): void
    {
        ++$this->pendingRequestsCount;
        if ($this->userId === $userId) {
            $this->lastRequestStatus = (int) $status;
        }
    }

    public function changeRequestStatus(string $userId, string $status): void
    {
        --$this->pendingRequestsCount;
        if ($this->userId === $userId) {
            $this->lastRequestStatus = (int) $status;
        }
    }

    public function addParticipant(string $participantId): void
    {
        ++$this->participantsCount;
        if ($this->userId === $participantId) {
            $this->isInvolved = true;
        }
    }

    public function removeParticipant(string $participantId): void
    {
        --$this->participantsCount;
        if ($this->userId === $participantId) {
            $this->isInvolved = false;
            $this->lastRequestStatus = null;
        }
    }

    public function createMemento(): ProjectListMemento
    {
        return new ProjectListMemento(
            $this->id,
            $this->name,
            $this->finishDate->getValue(),
            $this->ownerId,
            $this->ownerFullName,
            $this->status,
            $this->tasksCount,
            $this->participantsCount,
            $this->pendingRequestsCount,
            $this->isOwner(),
            $this->isInvolved,
            $this->lastRequestStatus
        );
    }

    public function getId(): string
    {
        return $this->id;
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
