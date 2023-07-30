<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Entity;

use TaskManager\Projections\Domain\DTO\TaskMemento;
use TaskManager\Shared\Domain\Hashable;
use TaskManager\Shared\Domain\ValueObject\DateTime;

final class TaskProjection implements Hashable
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private string $brief,
        private string $description,
        private DateTime $startDate,
        private DateTime $finishDate,
        private readonly string $ownerId,
        private int $status,
        private readonly string $projectId,
        private ?int $version
    ) {
    }

    public function getHash(): string
    {
        return $this->id;
    }

    public static function create(
        string $id,
        string $name,
        string $brief,
        string $description,
        string $startDate,
        string $finishDate,
        string $ownerId,
        string $status,
        string $projectId,
        ?int $version
    ): self {
        return new self(
            $id,
            $name,
            $brief,
            $description,
            new DateTime($startDate),
            new DateTime($finishDate),
            $ownerId,
            (int) $status,
            $projectId,
            $version
        );
    }

    public function changeInformation(
        string $name,
        string $brief,
        string $description,
        string $startDate,
        string $finishDate,
        ?int $version
    ): void {
        $this->name = $name;
        $this->brief = $brief;
        $this->description = $description;
        $this->startDate = new DateTime($startDate);
        $this->finishDate = new DateTime($finishDate);
        $this->version = $version;
    }

    public function changeStatus(string $status): void
    {
        $this->status = (int) $status;
    }

    public function createMemento(): TaskMemento
    {
        return new TaskMemento(
            $this->id,
            $this->name,
            $this->brief,
            $this->description,
            $this->startDate->getValue(),
            $this->finishDate->getValue(),
            $this->ownerId,
            $this->status,
            $this->version
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
