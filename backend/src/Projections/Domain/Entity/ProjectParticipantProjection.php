<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\ProjectParticipantMemento;
use TaskManager\Shared\Domain\Hashable;

final class ProjectParticipantProjection implements Hashable
{
    public function __construct(
        private readonly string $userId,
        private readonly string $projectId,
        private readonly string $userEmail,
        private string $userFirstname,
        private string $userLastname,
        private int $tasksCount
    ) {
    }

    public static function hash(string $projectId, string $userId): string
    {
        return $projectId.$userId;
    }

    public function getHash(): string
    {
        return self::hash($this->projectId, $this->userId);
    }

    public static function create(
        string $userId,
        string $projectId,
        string $userEmail,
        string $userFirstname,
        string $userLastname,
        int $tasksCount
    ): self {
        return new self(
            $userId,
            $projectId,
            $userEmail,
            $userFirstname,
            $userLastname,
            $tasksCount
        );
    }

    public function changeUserInformation(string $firstname, string $lastname): void
    {
        $this->userFirstname = $firstname;
        $this->userLastname = $lastname;
    }

    public function addTask(): void
    {
        ++$this->tasksCount;
    }

    public function createMemento(): ProjectParticipantMemento
    {
        return new ProjectParticipantMemento(
            $this->userId,
            $this->userEmail,
            $this->userFirstname,
            $this->userLastname,
            $this->tasksCount
        );
    }

    public function isForUser(string $userId): bool
    {
        return $this->userId === $userId;
    }
}
