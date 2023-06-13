<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Generator;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\ValueObject\ActiveTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;

final class TaskBuilder
{
    private TaskId $id;

    private ProjectId $projectId;

    private TaskName $name;

    private TaskBrief $brief;

    private TaskDescription $description;

    private TaskStartDate $startDate;

    private TaskFinishDate $finishDate;

    private TaskStatus $status;

    private TaskOwner $owner;

    private TaskInformation $information;

    public function __construct(private readonly Generator $faker)
    {
    }

    public function withId(TaskId $value): self
    {
        $this->id = $value;

        return $this;
    }

    public function withProjectId(ProjectId $value): self
    {
        $this->projectId = $value;

        return $this;
    }

    public function withName(TaskName $value): self
    {
        $this->name = $value;

        return $this;
    }

    public function withBrief(TaskBrief $value): self
    {
        $this->brief = $value;

        return $this;
    }

    public function withDescription(TaskDescription $value): self
    {
        $this->description = $value;

        return $this;
    }

    public function withStartDate(TaskStartDate $value): self
    {
        $this->startDate = $value;

        return $this;
    }

    public function withFinishDate(TaskFinishDate $value): self
    {
        $this->finishDate = $value;

        return $this;
    }

    public function withStatus(TaskStatus $value): self
    {
        $this->status = $value;

        return $this;
    }

    public function withOwner(TaskOwner $value): self
    {
        $this->owner = $value;

        return $this;
    }

    public function build(): Task
    {
        $this->prepareData();

        return new Task(
            $this->id,
            $this->projectId,
            $this->information,
            $this->status,
            $this->owner
        );
    }

    public function buildViaCreate(): Task
    {
        $this->prepareData();

        return Task::create(
            $this->id,
            $this->projectId,
            $this->information,
            $this->owner
        );
    }

    public function getId(): TaskId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    public function getName(): TaskName
    {
        return $this->name;
    }

    public function getBrief(): TaskBrief
    {
        return $this->brief;
    }

    public function getDescription(): TaskDescription
    {
        return $this->description;
    }

    public function getStartDate(): TaskStartDate
    {
        return $this->startDate;
    }

    public function getFinishDate(): TaskFinishDate
    {
        return $this->finishDate;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getOwner(): TaskOwner
    {
        return $this->owner;
    }

    private function prepareData(): void
    {
        $this->id = $this->id ?? new TaskId($this->faker->uuid());
        $this->projectId = $this->projectId ?? new ProjectId($this->faker->uuid());
        $this->name = $this->name ?? new TaskName($this->faker->regexify('.{255}'));
        $this->brief = $this->brief ?? new TaskBrief($this->faker->regexify('.{255}'));
        $this->description = $this->description ?? new TaskDescription($this->faker->regexify('.{255}'));
        $this->startDate = $this->startDate ?? new TaskStartDate();
        $this->finishDate = $this->finishDate ?? new TaskFinishDate();
        $this->information = new TaskInformation(
            $this->name,
            $this->brief,
            $this->description,
            $this->startDate,
            $this->finishDate,
        );
        $this->status = $this->status ?? new ActiveTaskStatus();
        $this->owner = $this->owner ?? new TaskOwner(new ProjectUserId($this->faker->uuid()));
    }
}
