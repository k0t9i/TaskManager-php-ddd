<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projects\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\InvalidProjectStatusTransitionException;
use TaskManager\Projects\Domain\Exception\ProjectModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectUser;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;

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
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->buildViaCreate();
        $events = $project->releaseEvents();

        $this->assertInstanceOf(Project::class, $project);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $builder->getName(),
            'description' => $builder->getDescription(),
            'finishDate' => $builder->getFinishDate(),
            'status' => ProjectStatus::STATUS_ACTIVE,
            'ownerId' => $builder->getOwner()->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformation()
    {
        $builder = new ProjectBuilder($this->faker);
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );

        $project = $builder->build();

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $builder->getOwner()->id
        );

        $events = $project->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'name' => $newInformation->name->value,
            'description' => $newInformation->description->value,
            'finishDate' => $newInformation->finishDate->getValue()
        ], $events[0]->toPrimitives());

        $project->changeInformation(
            null,
            null,
            null,
            $builder->getOwner()->id
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $builder->getOwner()->id
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $builder->getName(),
            null,
            $newInformation->finishDate,
            $builder->getOwner()->id
        );
        $events = $project->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertEquals([
            'name' => $builder->getName()->value,
            'description' => $newInformation->description->value,
            'finishDate' => $newInformation->finishDate->getValue()
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformationByNonOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $otherUserId
        );
    }

    public function testChangeInformationInClosedProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $builder->getOwner()->id
        );
    }

    public function testCloseProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();

        $project->close($builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    public function testActivateProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();

        $project->activate($builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_ACTIVE,
        ], $events[0]->toPrimitives());
    }

    public function testChangeToInvalidStatus()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();

        $this->expectException(InvalidProjectStatusTransitionException::class);
        $this->expectExceptionMessage(sprintf(
            'Project status "%s" cannot be changed to "%s"',
            ActiveProjectStatus::class,
            ActiveProjectStatus::class
        ));

        $project->activate($builder->getOwner()->id);
    }

    public function testChangeStatusByNonOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->close($otherUserId);
    }

    public function testChangeOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $project->changeOwner(new ProjectOwner($otherUserId), $builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectOwnerWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'ownerId' => $otherUserId->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeOwnerToAlreadyOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();

        $this->expectException(UserIsAlreadyProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project owner',
            $builder->getOwner()->id->value
        ));

        $project->changeOwner($builder->getOwner(), $builder->getOwner()->id);
    }

    public function testChangeOwnerToAlreadyParticipant()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectException(UserIsAlreadyProjectParticipantException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project participant',
            $builder->getParticipants()[0]->id
        ));

        $project->changeOwner(new ProjectOwner($builder->getParticipants()[0]->id), $builder->getOwner()->id);
    }

    public function testChangeOwnerByNonOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->changeOwner(new ProjectOwner($otherUserId), $otherUserId);
    }

    public function testChangeOwnerInClosedProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->changeOwner(new ProjectOwner($otherUserId), $builder->getOwner()->id);
    }

    public function testRemoveParticipant()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->removeParticipant($builder->getParticipants()[0], $builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testRemoveParticipantByNonOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->removeParticipant($builder->getParticipants()[0], $otherUserId);
    }

    public function testRemoveParticipantFromClosedProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->removeParticipant($builder->getParticipants()[0], $builder->getOwner()->id);
    }

    public function testRemoveNonExistenceParticipant()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUser = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectParticipantDoesNotExistException($otherUser);

        $project->removeParticipant($otherUser, $builder->getOwner()->id);
    }

    public function testLeaveProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->leaveProject($builder->getParticipants()[0]);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testLeaveClosedProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->leaveProject($builder->getParticipants()[0]);
    }

    public function testLeaveProjectByNonParticipant()
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUser = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $project = $builder
            ->withParticipant(new ProjectUser(
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectParticipantDoesNotExistException($otherUser);

        $project->leaveProject($otherUser);
    }

    private function expectProjectModificationIsNotAllowedException(): void
    {
        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));
    }

    private function expectUserIsNotProjectOwnerException(ProjectUserId $userId): void
    {
        $this->expectException(UserIsNotProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not project owner',
            $userId->value
        ));
    }

    private function expectProjectParticipantDoesNotExistException(ProjectUser $user): void
    {
        $this->expectException(ProjectParticipantDoesNotExistException::class);
        $this->expectExceptionMessage($message = sprintf(
            'Project participant "%s" doesn\'t exist',
            $user->id->value
        ));
    }
}
