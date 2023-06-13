<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\TaskStartDateIsGreaterThanFinishDateException;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;

class TaskTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->buildViaCreate();
        $events = $task->releaseEvents();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'projectId' => $builder->getProjectId(),
            'name' => $builder->getName(),
            'brief' => $builder->getBrief(),
            'description' => $builder->getDescription(),
            'startDate' => $builder->getStartDate(),
            'finishDate' => $builder->getFinishDate(),
            'status' => TaskStatus::STATUS_ACTIVE,
            'ownerId' => $builder->getOwner()->id,
        ], $events[0]->toPrimitives());
    }

    public function testCreateWithInvalidDates(): void
    {
        $builder = new TaskBuilder($this->faker);
        $builder
            ->withStartDate(new TaskStartDate('02-01-2023'))
            ->withFinishDate(new TaskFinishDate('01-01-2023'));

        $this->expectException(TaskStartDateIsGreaterThanFinishDateException::class);
        $this->expectExceptionMessage(sprintf(
            'Task start date "%s" is greater than finish date "%s"',
            $builder->getStartDate()->getValue(),
            $builder->getFinishDate()->getValue()
        ));

        $builder->buildViaCreate();
    }
}
