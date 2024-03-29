<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Generator;
use TaskManager\Projects\Domain\Collection\ParticipantCollection;
use TaskManager\Projects\Domain\Collection\ProjectTaskCollection;
use TaskManager\Projects\Domain\Collection\RequestCollection;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectTask;
use TaskManager\Shared\Domain\ValueObject\UserId;

final class ProjectBuilder
{
    private ProjectId $id;

    private ProjectName $name;

    private ProjectDescription $description;

    private ProjectFinishDate $finishDate;

    private ProjectStatus $status;

    private ProjectOwner $owner;

    /**
     * @var Participant[]
     */
    private array $participants;

    /**
     * @var Request[]
     */
    private array $requests;

    /**
     * @var ProjectTask[]
     */
    private array $tasks;

    private ProjectInformation $information;

    public function __construct(private readonly Generator $faker)
    {
    }

    public function withId(ProjectId $value): self
    {
        $this->id = $value;

        return $this;
    }

    public function withName(ProjectName $value): self
    {
        $this->name = $value;

        return $this;
    }

    public function withDescription(ProjectDescription $value): self
    {
        $this->description = $value;

        return $this;
    }

    public function withFinishDate(ProjectFinishDate $value): self
    {
        $this->finishDate = $value;

        return $this;
    }

    public function withStatus(ProjectStatus $value): self
    {
        $this->status = $value;

        return $this;
    }

    public function withOwner(ProjectOwner $value): self
    {
        $this->owner = $value;

        return $this;
    }

    public function withParticipant(Participant $value, bool $reset = false): self
    {
        if ($reset) {
            $this->participants = [];
        }
        $this->participants[] = $value;

        return $this;
    }

    public function withRequest(Request $value, bool $reset = false): self
    {
        if ($reset) {
            $this->requests = [];
        }
        $this->requests[] = $value;

        return $this;
    }

    public function withTask(ProjectTask $value, bool $reset = false): self
    {
        if ($reset) {
            $this->tasks = [];
        }
        $this->tasks[] = $value;

        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    public function build(): Project
    {
        $this->prepareData();

        $reflectionClass = new \ReflectionClass(Project::class);
        $constructor = $reflectionClass->getConstructor();
        $constructor->setAccessible(true);
        $project = $reflectionClass->newInstanceWithoutConstructor();
        $constructor->invokeArgs($project, [
            $this->id,
            $this->information,
            $this->status,
            $this->owner,
            new ParticipantCollection($this->participants),
            new RequestCollection($this->requests),
            new ProjectTaskCollection($this->tasks),
        ]);

        return $project;
    }

    public function buildViaCreate(): Project
    {
        $this->prepareData();

        return Project::create(
            $this->id,
            $this->information,
            $this->owner
        );
    }

    public function getId(): ProjectId
    {
        return $this->id;
    }

    public function getName(): ProjectName
    {
        return $this->name;
    }

    public function getDescription(): ProjectDescription
    {
        return $this->description;
    }

    public function getFinishDate(): ProjectFinishDate
    {
        return $this->finishDate;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function getOwner(): ProjectOwner
    {
        return $this->owner;
    }

    /**
     * @return Participant[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }

    /**
     * @return Request[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @return ProjectTask[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    private function prepareData(): void
    {
        $this->id = $this->id ?? new ProjectId($this->faker->uuid());
        $this->name = $this->name ?? new ProjectName($this->faker->regexify('.{255}'));
        $this->description = $this->description ?? new ProjectDescription($this->faker->regexify('.{255}'));
        $this->finishDate = $this->finishDate ?? new ProjectFinishDate();
        $this->information = new ProjectInformation(
            $this->name,
            $this->description,
            $this->finishDate,
        );
        $this->status = $this->status ?? new ActiveProjectStatus();
        $this->owner = $this->owner ?? new ProjectOwner(new UserId($this->faker->uuid()));
        $this->participants = $this->participants ?? [];
        $this->requests = $this->requests ?? [];
        $this->tasks = $this->tasks ?? [];
    }
}
