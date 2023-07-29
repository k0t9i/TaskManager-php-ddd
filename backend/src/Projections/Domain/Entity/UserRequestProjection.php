<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\UserRequestMemento;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class UserRequestProjection implements Hashable
{
    public function __construct(
        private readonly string $id,
        private readonly string $userId,
        private int $status,
        private DateTime $changeDate,
        private readonly string $projectId,
        private string $projectName
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }

    public static function create(
        string $id,
        string $userId,
        string $status,
        string $changeDate,
        string $projectId,
        string $projectName
    ): self {
        return new self(
            $id,
            $userId,
            (int) $status,
            new DateTime($changeDate),
            $projectId,
            $projectName
        );
    }

    public function changeStatus(string $status, string $changeDate): void
    {
        $this->status = (int) $status;
        $this->changeDate = new DateTime($changeDate);
    }

    public function changeProjectInformation(string $name): void
    {
        $this->projectName = $name;
    }

    public function createMemento(): UserRequestMemento
    {
        return new UserRequestMemento(
            $this->id,
            $this->status,
            $this->changeDate->getValue(),
            $this->projectId,
            $this->projectName
        );
    }

    public function isForProject(string $projectId): bool
    {
        return $this->projectId === $projectId;
    }
}
