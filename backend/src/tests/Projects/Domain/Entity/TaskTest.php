<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Collection\TaskLinkCollection;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projects\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projects\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\InvalidTaskStatusTransitionException;
use TaskManager\Projects\Domain\Exception\TaskLinkAlreadyExistsException;
use TaskManager\Projects\Domain\Exception\TaskLinkDoesNotExistException;
use TaskManager\Projects\Domain\Exception\TaskModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\TasksOfTaskLinkAreEqualException;
use TaskManager\Projects\Domain\Exception\TaskStartDateIsGreaterThanFinishDateException;
use TaskManager\Projects\Domain\Exception\UserIsNotTaskOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedTaskStatus;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskLink;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;
use TaskManager\Shared\Domain\ValueObject\DateTime;
use TaskManager\Shared\Domain\ValueObject\UserId;

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
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
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
            $newInfoBuilder->getInformation(),
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'name' => $newInfoBuilder->getName(),
            'brief' => $newInfoBuilder->getBrief(),
            'description' => $newInfoBuilder->getDescription(),
            'startDate' => $newInfoBuilder->getStartDate(),
            'finishDate' => $newInfoBuilder->getFinishDate(),
        ], $events[0]->toPrimitives());

        $task->changeInformation(
            $newInfoBuilder->getInformation(),
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);
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
            $newInfoBuilder->getInformation(),
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
            $newInfoBuilder->getInformation(),
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
        $otherUserId = new UserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->changeInformation(
            $newInfoBuilder->getInformation(),
            $otherUserId
        );
    }

    public function testClose(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $task->close($builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    public function testActivate(): void
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
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
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
        $otherUserId = new UserId($this->faker->uuid());
        $task = $builder->build();

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->close($otherUserId);
    }

    public function testCloseAsNeeded(): void
    {
        $performerId = new UserId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $task->closeAsNeeded($builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    /**
     * @throws \ReflectionException
     */
    public function testLimitDates(): void
    {
        $performerId = new UserId($this->faker->uuid());
        $startDate = '01-02-2023';
        $finishDate = '05-02-2023';
        $dateAfter = '10-02-2023';
        $dateBetween = '03-02-2023';
        $dateBefore = '10-01-2023';
        $builder = new TaskBuilder($this->faker);
        $builder
            ->withStartDate(new TaskStartDate($startDate))
            ->withFinishDate(new TaskFinishDate($finishDate))
            ->build();
        $task = $builder->build();
        $reflectionObject = new \ReflectionObject($task);
        $reflectionProperty = $reflectionObject->getProperty('information');

        $task->limitDates(new DateTime($dateAfter), $performerId);
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);
        /** @var TaskInformation $information */
        $information = $reflectionProperty->getValue($task);
        $this->assertEquals(new TaskFinishDate($finishDate), $information->finishDate);
        $this->assertEquals(new TaskStartDate($startDate), $information->startDate);

        $task->limitDates(new DateTime($dateBetween), $performerId);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($performerId->value, $events[0]->getPerformerId());
        /** @var TaskInformation $information */
        $information = $reflectionProperty->getValue($task);
        $this->assertEquals([
            'name' => $builder->getName(),
            'brief' => $builder->getBrief(),
            'description' => $builder->getDescription(),
            'startDate' => $information->startDate->getValue(),
            'finishDate' => $information->finishDate->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertEquals(new TaskFinishDate($dateBetween), $information->finishDate);
        $this->assertEquals(new TaskStartDate($startDate), $information->startDate);

        $task->limitDates(new DateTime($dateBefore), $performerId);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($performerId->value, $events[0]->getPerformerId());
        /** @var TaskInformation $information */
        $information = $reflectionProperty->getValue($task);
        $this->assertEquals([
            'name' => $builder->getName(),
            'brief' => $builder->getBrief(),
            'description' => $builder->getDescription(),
            'startDate' => $information->startDate->getValue(),
            'finishDate' => $information->finishDate->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertEquals(new TaskFinishDate($dateBefore), $information->finishDate);
        $this->assertEquals(new TaskStartDate($dateBefore), $information->startDate);
    }

    public function testCreateLink(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $task->createLink($linkedTaskId, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasCreated::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
    }

    public function testCreateLinkInClosedTask(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withStatus(new ClosedTaskStatus())
            ->build();

        $this->expectTaskModificationIsNotAllowedException();

        $task->createLink($linkedTaskId, $builder->getOwner()->id);
    }

    public function testCreateLinkToItself(): void
    {
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $this->expectException(TasksOfTaskLinkAreEqualException::class);
        $this->expectExceptionMessage(sprintf(
            'Link task "%s" to itself is forbidden',
            $task->getId()->value
        ));

        $task->createLink($task->getId(), $builder->getOwner()->id);
    }

    public function testCreateAlreadyExistingLink(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();

        $this->expectException(TaskLinkAlreadyExistsException::class);
        $this->expectExceptionMessage(sprintf(
            'Link from task "%s" to task "%s" already exists',
            $linkedTaskId->value,
            $taskId->value
        ));

        $task->createLink($linkedTaskId, $builder->getOwner()->id);
    }

    public function testCreateLinkByNonOwner(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $otherUserId = new UserId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->createLink($linkedTaskId, $otherUserId);
    }

    public function testDeleteLink(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();

        $task->deleteLink($linkedTaskId, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasDeleted::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
    }

    public function testDeleteLinkInClosedTask(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->withStatus(new ClosedTaskStatus())
            ->build();

        $this->expectTaskModificationIsNotAllowedException();

        $task->deleteLink($linkedTaskId, $builder->getOwner()->id);
    }

    public function testDeleteNonExistingLink(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $this->expectException(TaskLinkDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Link from task "%s" to task "%s" doesn\'t exist',
            $linkedTaskId->value,
            $task->getId()->value
        ));

        $task->deleteLink($linkedTaskId, $builder->getOwner()->id);
    }

    public function testDeleteLinkByNonOwner(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $otherUserId = new UserId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $task->deleteLink($linkedTaskId, $otherUserId);
    }

    public function testCreateBackLink(): void
    {
        $performerId = new UserId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();
        $reflectionObject = new \ReflectionObject($task);
        $reflectionProperty = $reflectionObject->getProperty('links');
        /** @var TaskLinkCollection $links */
        $links = $reflectionProperty->getValue($task);

        $task->createBackLink($linkedTaskId, $performerId);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasCreated::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($performerId->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
        $this->assertCount(1, $links->getItems());
        /** @var TaskLink $link */
        $link = $links->get($linkedTaskId->value);
        $this->assertEquals($linkedTaskId->value, $link->linkedTaskId);
    }

    public function testCreateAlreadyExistingBackLink(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();

        $task->createBackLink($linkedTaskId, new UserId($this->faker->uuid()));
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);
    }

    public function testDeleteBackLink(): void
    {
        $performerId = new UserId($this->faker->uuid());
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $reflectionObject = new \ReflectionObject($task);
        $reflectionProperty = $reflectionObject->getProperty('links');
        /** @var TaskLinkCollection $links */
        $links = $reflectionProperty->getValue($task);

        $task->deleteBackLink($linkedTaskId, $performerId);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasDeleted::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($performerId->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
        $this->assertCount(0, $links->getItems());
    }

    public function testDeleteNonExistingBackLink(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $builder = new TaskBuilder($this->faker);
        $task = $builder->build();

        $task->deleteBackLink($linkedTaskId, new UserId($this->faker->uuid()));
        $events = $task->releaseEvents();

        $this->assertCount(0, $events);
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
