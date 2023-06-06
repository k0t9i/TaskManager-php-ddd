<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use TaskManager\Projects\Domain\Entity\Project;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
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
}
