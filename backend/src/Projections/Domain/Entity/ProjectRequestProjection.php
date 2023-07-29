<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\ProjectRequestMemento;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class ProjectRequestProjection implements Hashable
{
    public function __construct(
        private readonly string $id,
        private readonly string $userId,
        private string $userFullName,
        private int $status,
        private DateTime $changeDate,
        private readonly string $projectId
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }

    public static function create(
        string $id,
        string $userId,
        string $userFullName,
        string $status,
        string $changeDate,
        string $projectId
    ): self {
        return new self(
            $id,
            $userId,
            $userFullName,
            (int) $status,
            new DateTime($changeDate),
            $projectId
        );
    }

    public function changeStatus(string $status, string $changeDate): void
    {
        $this->status = (int) $status;
        $this->changeDate = new DateTime($changeDate);
    }

    public function changeUserInformation(string $fullName): void
    {
        $this->userFullName = $fullName;
    }

    public function createMemento(): ProjectRequestMemento
    {
        return new ProjectRequestMemento(
            $this->id,
            $this->userId,
            $this->userFullName,
            $this->status,
            $this->changeDate->getValue()
        );
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
