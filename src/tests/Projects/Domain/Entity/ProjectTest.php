<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\ProjectModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\UserId;

class ProjectTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new UserId($this->faker->uuid()));

        $project = Project::create($id, $information, $owner);
        $events = $project->releaseEvents();

        $this->assertInstanceOf(Project::class, $project);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectWasCreatedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $information->name,
            'description' => $information->description,
            'finishDate' => $information->finishDate,
            'status' => ProjectStatus::STATUS_ACTIVE,
            'ownerId' => $owner->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformation()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new UserId($this->faker->uuid()));

        $project = new Project($id, $information, new ActiveProjectStatus(), $owner);

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->userId
        );

        $events = $project->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $newInformation->name->value,
            'description' => $newInformation->description->value,
            'finishDate' => $newInformation->finishDate->getValue()
        ], $events[0]->toPrimitives());

        $project->changeInformation(
            null,
            null,
            null,
            $owner->userId
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->userId
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $information->name,
            null,
            $newInformation->finishDate,
            $owner->userId
        );
        $events = $project->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertEquals([
            'name' => $information->name->value,
            'description' => $newInformation->description->value,
            'finishDate' => $newInformation->finishDate->getValue()
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformationByNonOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new UserId($this->faker->uuid()));
        $otherUserId = new UserId($this->faker->uuid());
        $project = new Project($id, $information, new ActiveProjectStatus(), $owner);

        $this->expectException(UserIsNotProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not project owner',
            $otherUserId->value
        ));

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $otherUserId
        );
    }

    public function testChangeInformationInClosedProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new UserId($this->faker->uuid()));
        $project = new Project($id, $information, new ClosedProjectStatus(), $owner);

        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->userId
        );
    }
}
