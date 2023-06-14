<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\InvalidTaskStatusTransitionException;
use TaskManager\Projects\Domain\Exception\TaskModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\TaskStartDateIsGreaterThanFinishDateException;
use TaskManager\Projects\Domain\Exception\UserIsNotTaskOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
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

        $this->expectTaskStartDateIsGreaterThanFinishDateException(
            $builder->getStartDate()->getValue(),
            $builder->getFinishDate()->getValue()
        );

        $builder->buildViaCreate();
    }

    public function testChangeInformation(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();
        $newInfoBuilder = new TaskBuilder($this->faker);
        $newInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $task->changeInformation(
            $newInfoBuilder->getName(),
            $newInfoBuilder->getBrief(),
            $newInfoBuilder->getDescription(),
            $newInfoBuilder->getStartDate(),
            $newInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $newInfoBuilder->getName(),
            'brief' => $newInfoBuilder->getBrief(),
            'description' => $newInfoBuilder->getDescription(),
            'startDate' => $newInfoBuilder->getStartDate(),
            'finishDate' => $newInfoBuilder->getFinishDate(),
        ], $events[0]->toPrimitives());

        $task->changeInformation(
            null,
            null,
            null,
            null,
            null,
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);

        $task->changeInformation(
            $newInfoBuilder->getName(),
            $newInfoBuilder->getBrief(),
            $newInfoBuilder->getDescription(),
            $newInfoBuilder->getStartDate(),
            $newInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);

        $task->changeInformation(
            $builder->getName(),
            null,
            $builder->getDescription(),
            $newInfoBuilder->getStartDate(),
            null,
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $builder->getName(),
            'brief' => $newInfoBuilder->getBrief(),
            'description' => $builder->getDescription(),
            'startDate' => $newInfoBuilder->getStartDate(),
            'finishDate' => $newInfoBuilder->getFinishDate(),
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformationInClosedTask(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withStatus(new ClosedTaskStatus())
            ->build();
        $newInfoBuilder = new TaskBuilder($this->faker);
        $newInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectTaskModificationIsNotAllowedException();

        $task->changeInformation(
            $newInfoBuilder->getName(),
            $newInfoBuilder->getBrief(),
            $newInfoBuilder->getDescription(),
            $newInfoBuilder->getStartDate(),
            $newInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeInformationWithInvalidDates(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();
        $newInfoBuilder = new TaskBuilder($this->faker);
        $newInfoBuilder
            ->withStartDate(new TaskStartDate('03-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectTaskStartDateIsGreaterThanFinishDateException(
            $newInfoBuilder->getStartDate()->getValue(),
            $newInfoBuilder->getFinishDate()->getValue()
        );

        $task->changeInformation(
            $newInfoBuilder->getName(),
            $newInfoBuilder->getBrief(),
            $newInfoBuilder->getDescription(),
            $newInfoBuilder->getStartDate(),
            $newInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeInformationByNonOwner(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();
        $newInfoBuilder = new TaskBuilder($this->faker);
        $newInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->changeInformation(
            $newInfoBuilder->getName(),
            $newInfoBuilder->getBrief(),
            $newInfoBuilder->getDescription(),
            $newInfoBuilder->getStartDate(),
            $newInfoBuilder->getFinishDate(),
            $otherUserId
        );
    }

    public function testCloseTask(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $task->close($builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    public function testActivateTask(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withStatus(new ClosedTaskStatus())
            ->build();

        $task->activate($builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_ACTIVE,
        ], $events[0]->toPrimitives());
    }

    public function testChangeToInvalidStatus(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $this->expectException(InvalidTaskStatusTransitionException::class);
        $this->expectExceptionMessage(sprintf(
            'Task status "%s" cannot be changed to "%s"',
            ActiveTaskStatus::class,
            ActiveTaskStatus::class
        ));

        $task->activate($builder->getOwner()->id);
    }

    public function testChangeStatusByNonOwner(): void
    {
        $builder = new TaskBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $task = $builder->build();

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->close($otherUserId);
    }

    private function expectTaskModificationIsNotAllowedException(): void
    {
        $this->expectException(TaskModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Task modification is not allowed when status is "%s"',
            ClosedTaskStatus::class
        ));
    }

    private function expectTaskStartDateIsGreaterThanFinishDateException(string $startDate, string $finishDate): void
    {
        $this->expectException(TaskStartDateIsGreaterThanFinishDateException::class);
        $this->expectExceptionMessage(sprintf(
            'Task start date "%s" is greater than finish date "%s"',
            $startDate,
            $finishDate
        ));
    }

    private function expectUserIsNotTaskOwnerException(string $userId): void
    {
        $this->expectException(UserIsNotTaskOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not task owner',
            $userId
        ));
    }
}
