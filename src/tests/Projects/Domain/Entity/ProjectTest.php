<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Collection\ParticipantCollection;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projects\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskFinishDateWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskWasClosedEvent;
use TaskManager\Projects\Domain\Event\ProjectTaskWasCreatedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projects\Domain\Event\TaskInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projects\Domain\Event\TaskLinkWasDeleted;
use TaskManager\Projects\Domain\Event\TaskStatusWasChangedEvent;
use TaskManager\Projects\Domain\Exception\InvalidProjectStatusTransitionException;
use TaskManager\Projects\Domain\Exception\ProjectModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\ProjectTaskDoesNotExistException;
use TaskManager\Projects\Domain\Exception\ProjectUserDoesNotExistException;
use TaskManager\Projects\Domain\Exception\ProjectUserHasTaskException;
use TaskManager\Projects\Domain\Exception\RequestDoesNotExistException;
use TaskManager\Projects\Domain\Exception\TaskFinishDateIsGreaterThanProjectFinishDateException;
use TaskManager\Projects\Domain\Exception\TaskStartDateIsGreaterThanProjectFinishDateException;
use TaskManager\Projects\Domain\Exception\UserAlreadyHasPendingRequestException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsNotTaskOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedTaskStatus;
use TaskManager\Projects\Domain\ValueObject\ConfirmedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\PendingRequestStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectTask;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestChangeDate;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskLink;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Projects\Domain\ValueObject\TaskStatus;

class ProjectTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->buildViaCreate();
        $events = $project->releaseEvents();

        $this->assertInstanceOf(Project::class, $project);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'name' => $builder->getName(),
            'description' => $builder->getDescription(),
            'finishDate' => $builder->getFinishDate(),
            'status' => ProjectStatus::STATUS_ACTIVE,
            'ownerId' => $builder->getOwner()->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformation(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $newInformation = new ProjectInformation(
            new ProjectName($this->faker->regexify('.{255}')),
            new ProjectDescription($this->faker->regexify('.{255}')),
            new ProjectFinishDate(),
        );

        $ownerId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withOwner(new ProjectOwner($ownerId))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                $ownerId
            ))
            ->build();

        $project->changeInformation(
            $newInformation->name,
            $newInformation->description,
            $newInformation->finishDate,
            $builder->getOwner()->id
        );

        $events = $project->releaseEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(ProjectTaskFinishDateWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'taskId' => $builder->getTasks()[0]->taskId->value,
            'finishDate' => $newInformation->finishDate->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertInstanceOf(ProjectInformationWasChangedEvent::class, $events[1]);
        $this->assertEquals($builder->getId()->value, $events[1]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'name' => $newInformation->name->value,
            'description' => $newInformation->description->value,
            'finishDate' => $newInformation->finishDate->getValue(),
        ], $events[1]->toPrimitives());

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
            'finishDate' => $newInformation->finishDate->getValue(),
        ], $events[0]->toPrimitives());
    }

    public function testChangeInformationByNonOwner(): void
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

    public function testChangeInformationInClosedProject(): void
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

    public function testCloseProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $ownerId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withOwner(new ProjectOwner($ownerId))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                $ownerId
            ))
            ->build();

        $project->close($builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(ProjectStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
        $this->assertInstanceOf(ProjectTaskWasClosedEvent::class, $events[1]);
        $this->assertEquals($builder->getId()->value, $events[1]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'taskId' => $builder->getTasks()[0]->taskId->value,
        ], $events[1]->toPrimitives());
    }

    public function testActivateProject(): void
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
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => ProjectStatus::STATUS_ACTIVE,
        ], $events[0]->toPrimitives());
    }

    public function testChangeToInvalidStatus(): void
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

    public function testChangeStatusByNonOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->close($otherUserId);
    }

    public function testChangeOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $project->changeOwner(new ProjectOwner($otherUserId), $builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectOwnerWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'ownerId' => $otherUserId->value,
        ], $events[0]->toPrimitives());
    }

    public function testChangeOwnerToAlreadyOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();

        $this->expectUserIsAlreadyProjectOwnerException($builder->getOwner()->id);

        $project->changeOwner($builder->getOwner(), $builder->getOwner()->id);
    }

    public function testChangeOwnerToAlreadyParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectUserIsAlreadyProjectParticipantException($builder->getParticipants()[0]->userId);

        $project->changeOwner(new ProjectOwner($builder->getParticipants()[0]->userId), $builder->getOwner()->id);
    }

    public function testChangeOwnerByNonOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->changeOwner(new ProjectOwner($otherUserId), $otherUserId);
    }

    public function testChangeOwnerInClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->changeOwner(new ProjectOwner($otherUserId), $builder->getOwner()->id);
    }

    public function testChangeOwnerWithTask(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $ownerId = new ProjectUserId($this->faker->uuid());
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withOwner(new ProjectOwner($ownerId))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                $ownerId
            ))
            ->build();

        $this->expectProjectUserHasTaskException($ownerId, $builder->getId());

        $project->changeOwner(new ProjectOwner($otherUserId), $builder->getOwner()->id);
    }

    public function testRemoveParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->removeParticipant($builder->getParticipants()[0]->userId, $builder->getOwner()->id);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testRemoveParticipantByNonOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->removeParticipant($builder->getParticipants()[0]->userId, $otherUserId);
    }

    public function testRemoveParticipantFromClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->removeParticipant($builder->getParticipants()[0]->userId, $builder->getOwner()->id);
    }

    public function testRemoveNonExistingParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectParticipantDoesNotExistException($otherUserId);

        $project->removeParticipant($otherUserId, $builder->getOwner()->id);
    }

    public function testRemoveParticipantWithTask(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $participantId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                $participantId
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                $participantId
            ))
            ->build();

        $this->expectProjectUserHasTaskException($participantId, $builder->getId());

        $project->removeParticipant($participantId, $builder->getOwner()->id);
    }

    public function testLeaveProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->leaveProject($builder->getParticipants()[0]->userId);
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectParticipantWasRemovedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getParticipants()[0]->userId->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testLeaveClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->leaveProject($builder->getParticipants()[0]->userId);
    }

    public function testLeaveProjectByNonParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $otherUserId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectParticipantDoesNotExistException($otherUserId);

        $project->leaveProject($otherUserId);
    }

    public function testLeaveProjectWithTask(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $participantId = new ProjectUserId($this->faker->uuid());
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                $participantId
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                $participantId
            ))
            ->build();

        $this->expectProjectUserHasTaskException($participantId, $builder->getId());

        $project->leaveProject($participantId);
    }

    public function testCreateRequest(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();
        $requestId = new RequestId($this->faker->uuid());
        $userId = new ProjectUserId($this->faker->uuid());

        $request = $project->createRequest($requestId, $userId);
        $events = $project->releaseEvents();

        $this->assertInstanceOf(Request::class, $request);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(RequestWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($userId->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'requestId' => $request->getId()->value,
            'userId' => $request->getUserId()->value,
            'status' => $request->getStatus()->getScalar(),
            'changeDate' => $request->getChangeDate()->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertEquals($requestId, $request->getId());
        $this->assertEquals($userId, $request->getUserId());
    }

    public function testCreateRequestInClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();
        $requestId = new RequestId($this->faker->uuid());
        $userId = new ProjectUserId($this->faker->uuid());

        $this->expectProjectModificationIsNotAllowedException();

        $project->createRequest($requestId, $userId);
    }

    public function testCreateRequestByProjectOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();
        $requestId = new RequestId($this->faker->uuid());

        $this->expectUserIsAlreadyProjectOwnerException($builder->getOwner()->id);

        $project->createRequest($requestId, $builder->getOwner()->id);
    }

    public function testCreateRequestByProjectParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $requestId = new RequestId($this->faker->uuid());

        $this->expectUserIsAlreadyProjectParticipantException($builder->getParticipants()[0]->userId);

        $project->createRequest($requestId, $builder->getParticipants()[0]->userId);
    }

    public function testCreateRequestByProjectParticipantWithPendingRequest(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();
        $requestId = new RequestId($this->faker->uuid());

        $this->expectException(UserAlreadyHasPendingRequestException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" already has request to project "%s"',
            $builder->getRequests()[0]->getUserId()->value,
            $builder->getId()->value
        ));

        $project->createRequest($requestId, $builder->getRequests()[0]->getUserId());
    }

    public function testRejectRequest(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();

        $project->rejectRequest(
            $builder->getRequests()[0]->getId(),
            $builder->getOwner()->id
        );
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(RequestStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'requestId' => $builder->getRequests()[0]->getId()->value,
            'userId' => $builder->getRequests()[0]->getUserId()->value,
            'status' => RequestStatus::STATUS_REJECTED,
            'changeDate' => $builder->getRequests()[0]->getChangeDate()->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertInstanceOf(RejectedRequestStatus::class, $builder->getRequests()[0]->getStatus());
    }

    /**
     * @throws \ReflectionException
     */
    public function testConfirmRequest(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();
        $reflection = new \ReflectionObject($project);

        $project->confirmRequest(
            $builder->getRequests()[0]->getId(),
            $builder->getOwner()->id
        );
        /** @var ParticipantCollection $participantCollection */
        $participantCollection = $reflection->getProperty('participants')->getValue($project);
        /** @var Participant[] $participants */
        $participants = $participantCollection->getItems();
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(RequestStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'requestId' => $builder->getRequests()[0]->getId()->value,
            'userId' => $builder->getRequests()[0]->getUserId()->value,
            'status' => RequestStatus::STATUS_CONFIRMED,
            'changeDate' => $builder->getRequests()[0]->getChangeDate()->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertInstanceOf(ConfirmedRequestStatus::class, $builder->getRequests()[0]->getStatus());
        $this->assertCount(1, $participants);
        $this->assertEquals($builder->getRequests()[0]->getUserId(), $participants[0]->userId);
    }

    public function testChangeRequestStatusInClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->rejectRequest(
            $builder->getRequests()[0]->getId(),
            $builder->getOwner()->id
        );
    }

    public function testChangeRequestStatusByNonOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotProjectOwnerException($otherUserId);

        $project->rejectRequest(
            $builder->getRequests()[0]->getId(),
            $otherUserId
        );
    }

    public function testChangeNonExistingRequestStatus(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();
        $requestId = new RequestId($this->faker->uuid());

        $this->expectException(RequestDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Request "%s" to project "%s" doesn\'t exist',
            $requestId->value,
            $builder->getId()->value
        ));

        $project->rejectRequest(
            $requestId,
            $builder->getOwner()->id
        );
    }

    public function testCreateTaskForOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $task = $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($builder->getOwner()->id)
        );

        $this->assertInstanceOf(Task::class, $task);
    }

    public function testCreateTaskForParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $task = $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($builder->getParticipants()[0]->userId)
        );

        $this->assertInstanceOf(Task::class, $task);
    }

    public function testCreateTaskInClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($builder->getOwner()->id)
        );
    }

    public function testCreateTaskForNonProjectUser(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectProjectUserDoesNotExistException($otherUserId);

        $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($otherUserId)
        );
    }

    public function testCreateTaskWithStartDateGreaterThanProjectFinishDate(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-02-2023'))
            ->withFinishDate(new TaskFinishDate('02-02-2023'))
            ->build();

        $this->expectTaskStartDateIsGreaterThanProjectFinishDateException(
            $taskBuilder->getStartDate()->getValue(),
            $builder->getFinishDate()->getValue()
        );

        $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($builder->getOwner()->id)
        );
    }

    public function testCreateTaskWithFinishDateGreaterThanProjectFinishDate(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('31-01-2023'))
            ->build();
        $taskBuilder = new TaskBuilder($this->faker);
        $taskBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-02-2023'))
            ->build();

        $this->expectTaskFinishDateIsGreaterThanProjectFinishDateException(
            $taskBuilder->getFinishDate()->getValue(),
            $builder->getFinishDate()->getValue()
        );

        $project->createTask(
            $taskBuilder->getId(),
            $taskBuilder->getInformation(),
            new TaskOwner($builder->getOwner()->id)
        );
    }

    public function testAddProjectTaskForOwner(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();
        $taskId = new TaskId($this->faker->uuid());

        $project->addProjectTask(
            $taskId,
            $builder->getOwner()->id
        );
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectTaskWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'taskId' => $taskId->value,
            'ownerId' => $builder->getOwner()->id->value,
        ], $events[0]->toPrimitives());
    }

    public function testAddProjectTaskForParticipant(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withParticipant(new Participant(
                new ProjectId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $taskId = new TaskId($this->faker->uuid());

        $project->addProjectTask(
            $taskId,
            $builder->getParticipants()[0]->userId
        );
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProjectTaskWasCreatedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'taskId' => $taskId->value,
            'ownerId' => $builder->getParticipants()[0]->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testAddProjectTaskForNonProjectUser(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectProjectUserDoesNotExistException($otherUserId);

        $project->addProjectTask(
            new TaskId($this->faker->uuid()),
            $otherUserId
        );
    }

    public function testAddProjectTaskInClosedProject(): void
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->addProjectTask(
            new TaskId($this->faker->uuid()),
            $builder->getOwner()->id
        );
    }

    public function testChangeTaskInformation(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskInformationWasChangedEvent::class, $events[0]);
        $this->assertEquals($taskBuilder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'name' => $newTaskInfoBuilder->getName(),
            'brief' => $newTaskInfoBuilder->getBrief(),
            'description' => $newTaskInfoBuilder->getDescription(),
            'startDate' => $newTaskInfoBuilder->getStartDate(),
            'finishDate' => $newTaskInfoBuilder->getFinishDate(),
        ], $events[0]->toPrimitives());
    }

    public function testChangeTaskInformationInClosedProject(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeTaskInformationForNonExistingProjectTask(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectProjectTaskDoesNotExistException($task->getId()->value);

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeTaskInformationWithStartDateGreaterThanProjectFinishDate(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-03-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();

        $this->expectTaskStartDateIsGreaterThanProjectFinishDateException(
            $newTaskInfoBuilder->getStartDate()->getValue(),
            $builder->getFinishDate()->getValue()
        );

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeTaskInformationWithFinishDateGreaterThanProjectFinishDate(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-03-2023'))
            ->build();

        $this->expectTaskFinishDateIsGreaterThanProjectFinishDateException(
            $newTaskInfoBuilder->getFinishDate()->getValue(),
            $builder->getFinishDate()->getValue()
        );

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $builder->getOwner()->id
        );
    }

    public function testChangeTaskInformationByNonOwner(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withFinishDate(new ProjectFinishDate('01-02-2023'))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $newTaskInfoBuilder = new TaskBuilder($this->faker);
        $newTaskInfoBuilder
            ->withStartDate(new TaskStartDate('01-01-2023'))
            ->withFinishDate(new TaskFinishDate('02-01-2023'))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $project->changeTaskInformation(
            $task,
            $newTaskInfoBuilder->getName(),
            $newTaskInfoBuilder->getBrief(),
            $newTaskInfoBuilder->getDescription(),
            $newTaskInfoBuilder->getStartDate(),
            $newTaskInfoBuilder->getFinishDate(),
            $otherUserId
        );
    }

    public function testActivateTask(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withStatus(new ClosedTaskStatus())
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->activateTask($task, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($taskBuilder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_ACTIVE,
        ], $events[0]->toPrimitives());
    }

    public function testActivateTaskInClosedProject(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withStatus(new ClosedTaskStatus())
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->activateTask($task, $builder->getOwner()->id);
    }

    public function testActivateNonExistingTask(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withStatus(new ClosedTaskStatus())
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($task->getId()->value);

        $project->activateTask($task, $builder->getOwner()->id);
    }

    public function testActivateTaskByNonOwner(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withStatus(new ClosedTaskStatus())
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $project->activateTask($task, $otherUserId);
    }

    public function testCloseTask(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->closeTask($task, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($taskBuilder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'status' => TaskStatus::STATUS_CLOSED,
        ], $events[0]->toPrimitives());
    }

    public function testCloseTaskInClosedProject(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->closeTask($task, $builder->getOwner()->id);
    }

    public function testCloseNonExistingTask(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                new TaskId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($task->getId()->value);

        $project->closeTask($task, $builder->getOwner()->id);
    }

    public function testCloseTaskByNonOwner(): void
    {
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $project->closeTask($task, $otherUserId);
    }

    public function testCreateTaskLink(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->createTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasCreated::class, $events[0]);
        $this->assertEquals($taskBuilder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
    }

    public function testCreateTaskLinkInClosedProject(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->createTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testCreateTaskLinkFromNonExistingProjectTask(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($task->getId()->value);

        $project->createTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testCreateTaskLinkToNonExistingProjectTask(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($linkedTaskId->value);

        $project->createTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testCreateTaskLinkByNonOwner(): void
    {
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $project->createTaskLink($task, $linkedTaskId, $otherUserId);
    }

    public function testDeleteTaskLink(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $project->deleteTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
        $events = $task->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskLinkWasDeleted::class, $events[0]);
        $this->assertEquals($taskBuilder->getId()->value, $events[0]->getAggregateId());
        $this->assertEquals($builder->getOwner()->id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'linkedTaskId' => $linkedTaskId->value,
        ], $events[0]->toPrimitives());
    }

    public function testDeleteTaskLinkInClosedProject(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectModificationIsNotAllowedException();

        $project->deleteTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testDeleteTaskLinkFromNonExistingProjectTask(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($task->getId()->value);

        $project->deleteTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testDeleteTaskLinkToNonExistingProjectTask(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $task->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();

        $this->expectProjectTaskDoesNotExistException($linkedTaskId->value);

        $project->deleteTaskLink($task, $linkedTaskId, $builder->getOwner()->id);
    }

    public function testDeleteTaskLinkByNonOwner(): void
    {
        $taskId = new TaskId($this->faker->uuid());
        $linkedTaskId = new TaskId($this->faker->uuid());
        $taskBuilder = new TaskBuilder($this->faker);
        $task = $taskBuilder
            ->withId($taskId)
            ->withTaskLink(new TaskLink(
                $taskId,
                $linkedTaskId
            ))
            ->build();
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $linkedTaskId,
                new ProjectUserId($this->faker->uuid())
            ))
            ->withTask(new ProjectTask(
                new ProjectId($this->faker->uuid()),
                $taskBuilder->getId(),
                new ProjectUserId($this->faker->uuid())
            ))
            ->build();
        $otherUserId = new ProjectUserId($this->faker->uuid());

        $this->expectUserIsNotTaskOwnerException($otherUserId->value);

        $project->deleteTaskLink($task, $linkedTaskId, $otherUserId);
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

    private function expectProjectParticipantDoesNotExistException(ProjectUserId $userId): void
    {
        $this->expectException(ProjectParticipantDoesNotExistException::class);
        $this->expectExceptionMessage($message = sprintf(
            'Project participant "%s" doesn\'t exist',
            $userId->value
        ));
    }

    private function expectUserIsAlreadyProjectOwnerException(ProjectUserId $id): void
    {
        $this->expectException(UserIsAlreadyProjectOwnerException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project owner',
            $id->value
        ));
    }

    private function expectUserIsAlreadyProjectParticipantException(ProjectUserId $id): void
    {
        $this->expectException(UserIsAlreadyProjectParticipantException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" is already project participant',
            $id->value
        ));
    }

    private function expectProjectUserHasTaskException(ProjectUserId $userId, ProjectId $projectId): void
    {
        $this->expectException(ProjectUserHasTaskException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" has task(s) in project "%s',
            $userId->value,
            $projectId->value
        ));
    }

    private function expectProjectUserDoesNotExistException(ProjectUserId $userId): void
    {
        $this->expectException(ProjectUserDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Project user "%s" doesn\'t exist',
            $userId->value
        ));
    }

    private function expectTaskStartDateIsGreaterThanProjectFinishDateException(
        string $startDate,
        string $projectFinishDate
    ): void {
        $this->expectException(TaskStartDateIsGreaterThanProjectFinishDateException::class);
        $this->expectExceptionMessage(sprintf(
            'Task start date "%s" is greater than project finish date "%s"',
            $startDate,
            $projectFinishDate
        ));
    }

    private function expectTaskFinishDateIsGreaterThanProjectFinishDateException(
        string $finishDate,
        string $projectFinishDate
    ): void {
        $this->expectException(TaskFinishDateIsGreaterThanProjectFinishDateException::class);
        $this->expectExceptionMessage(sprintf(
            'Task finish date "%s" is greater than project finish date "%s"',
            $finishDate,
            $projectFinishDate
        ));
    }

    private function expectProjectTaskDoesNotExistException(string $taskId): void
    {
        $this->expectException(ProjectTaskDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Project task "%s" doesn\'t exist',
            $taskId
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
