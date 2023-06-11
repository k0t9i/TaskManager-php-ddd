<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
use TaskManager\Projects\Domain\ValueObject\ProjectId;
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
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
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
            'ownerId' => $owner->id->value,
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
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));

        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->id
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
            $owner->id
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->id
        );
        $events = $project->releaseEvents();
        $this->assertCount(0, $events);

        $project->changeInformation(
            $information->name,
            null,
            $newInformation->finishDate,
            $owner->id
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
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

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
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $project = new Project(
            $id,
            $information,
            new ClosedProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $owner->id
        );
    }

    public function testCloseProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $project->close($owner->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    public function testActivateProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $project = new Project(
            $id,
            $information,
            new ClosedProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $project->activate($owner->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_ACTIVE,
        ], $events[0]->toPrimitives());
    }

    public function testChangeToInvalidStatus()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(InvalidProjectStatusTransitionException::class);
        $this->expectExceptionMessage(sprintf(
            'Status "%s" cannot be changed to "%s"',
            ActiveProjectStatus::class,
            ActiveProjectStatus::class
        ));

        $project->activate($owner->id);
    }

    public function testChangeStatusByNonOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(UserIsNotProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not project owner',
            $otherUserId->value
        ));

        $project->close($otherUserId);
    }

    public function testChangeOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $project->changeOwner(new ProjectOwner($otherUserId), $owner->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectOwnerWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'ownerId' => $otherUserId->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeOwnerToAlreadyOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(UserIsAlreadyProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project owner',
            $owner->id->value
        ));

        $project->changeOwner($owner, $owner->id);
    }

    public function testChangeOwnerToAlreadyParticipant()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(UserIsAlreadyProjectParticipantException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project participant',
            $participant->id
        ));

        $project->changeOwner(new ProjectOwner($participant->id), $owner->id);
    }

    public function testChangeOwnerByNonOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(UserIsNotProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not project owner',
            $otherUserId->value
        ));

        $project->changeOwner(new ProjectOwner($otherUserId), $otherUserId);
    }

    public function testChangeOwnerInClosedProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = new Project(
            $id,
            $information,
            new ClosedProjectStatus(),
            $owner,
            new ArrayCollection()
        );

        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));

        $project->changeOwner(new ProjectOwner($otherUserId), $owner->id);
    }

    public function testRemoveParticipant()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $project->removeParticipant($participant, $owner->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'participantId' => $participant->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testRemoveParticipantByNonOwner()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(UserIsNotProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is not project owner',
            $otherUserId->value
        ));

        $project->removeParticipant($participant, $otherUserId);
    }

    public function testRemoveParticipantFromClosedProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ClosedProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));

        $project->removeParticipant($participant, $owner->id);
    }

    public function testRemoveNonExistenceParticipant()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUser = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(ProjectParticipantDoesNotExistException::class);
        $this->expectExceptionMessage($message = sprintf(
            'Project participant "%s" doesn\'t exist',
            $otherUser->id->value
        ));

        $project->removeParticipant($otherUser, $owner->id);
    }

    public function testLeaveProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $project->leaveProject($participant);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'participantId' => $participant->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testLeaveClosedProject()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ClosedProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(ProjectModificationIsNotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'Project modification is not allowed when status is "%s"',
            ClosedProjectStatus::class
        ));

        $project->leaveProject($participant);
    }

    public function testLeaveProjectByNonParticipant()
    {
        $id = new ProjectId($this->faker->uuid());
        $information = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );
        $owner = new ProjectOwner(new ProjectUserId($this->faker->uuid()));
        $otherUser = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participant = new ProjectUser(new ProjectUserId($this->faker->uuid()));
        $participants = new ArrayCollection([$participant]);
        $project = new Project(
            $id,
            $information,
            new ActiveProjectStatus(),
            $owner,
            $participants
        );

        $this->expectException(ProjectParticipantDoesNotExistException::class);
        $this->expectExceptionMessage($message = sprintf(
            'Project participant "%s" doesn\'t exist',
            $otherUser->id->value
        ));

        $project->leaveProject($otherUser);
    }
}
