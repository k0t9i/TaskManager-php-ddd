<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Doctrine\Common\Collections\Collection;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\Event\ProjectInformationWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectOwnerWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectParticipantWasRemovedEvent;
use TaskManager\Projects\Domain\Event\ProjectStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\Event\RequestStatusWasChangedEvent;
use TaskManager\Projects\Domain\Event\RequestWasCreatedEvent;
use TaskManager\Projects\Domain\Exception\InvalidProjectStatusTransitionException;
use TaskManager\Projects\Domain\Exception\ProjectModificationIsNotAllowedException;
use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\RequestDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserAlreadyHasPendingRequestException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectOwnerException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\Exception\UserIsNotProjectOwnerException;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ClosedProjectStatus;
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
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestChangeDate;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;

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

        $this->expectUserIsAlreadyProjectOwnerException($builder->getOwner()->id);

        $project->changeOwner($builder->getOwner(), $builder->getOwner()->id);
    }

    public function testChangeOwnerToAlreadyParticipant()
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
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testRemoveParticipantByNonOwner()
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

    public function testRemoveParticipantFromClosedProject()
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

    public function testRemoveNonExistingParticipant()
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

    public function testLeaveProject()
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
        $this->assertEquals([
            'participantId' => $builder->getParticipants()[0]->userId->value,
        ], $events[0]->toPrimitives());
    }

    public function testLeaveClosedProject()
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

    public function testLeaveProjectByNonParticipant()
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

    public function testCreateRequest()
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
        $this->assertEquals([
            'requestId' => $request->getId()->value,
            'userId' => $request->getUserId()->value,
            'status' => $request->getStatus()->getScalar(),
            'changeDate' => $request->getChangeDate()->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertEquals($requestId, $request->getId());
        $this->assertEquals($userId, $request->getUserId());
    }

    public function testCreateRequestInClosedProject()
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

    public function testCreateRequestByProjectOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder->build();
        $requestId = new RequestId($this->faker->uuid());

        $this->expectUserIsAlreadyProjectOwnerException($builder->getOwner()->id);

        $project->createRequest($requestId, $builder->getOwner()->id);
    }

    public function testCreateRequestByProjectParticipant()
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

    public function testCreateRequestByProjectParticipantWithPendingRequest()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
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

    public function testRejectRequest()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
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
        $this->assertEquals([
            'requestId' => $builder->getRequests()[0]->getId()->value,
            'userId' => $builder->getRequests()[0]->getUserId()->value,
            'status' => RequestStatus::STATUS_REJECTED,
            'changeDate' => $builder->getRequests()[0]->getChangeDate()->getValue(),
        ], $events[0]->toPrimitives());
        $this->assertInstanceOf(RejectedRequestStatus::class, $builder->getRequests()[0]->getStatus());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testConfirmRequest()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
                new ProjectUserId($this->faker->uuid()),
                new PendingRequestStatus(),
                new RequestChangeDate()
            ))
            ->build();
        $reflection = new ReflectionObject($project);

        $project->confirmRequest(
            $builder->getRequests()[0]->getId(),
            $builder->getOwner()->id
        );
        /** @var Collection $participantCollection */
        $participantCollection = $reflection->getProperty('participants')->getValue($project);
        /** @var Participant[] $participants */
        $participants = $participantCollection->toArray();
        $events = $project->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(RequestStatusWasChangedEvent::class, $events[0]);
        $this->assertEquals($builder->getId()->value, $events[0]->getAggregateId());
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

    public function testChangeRequestStatusInClosedProject()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withStatus(new ClosedProjectStatus())
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
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

    public function testChangeRequestStatusByNonOwner()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
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

    public function testChangeNonExistingRequestStatus()
    {
        $builder = new ProjectBuilder($this->faker);
        $project = $builder
            ->withRequest(new Request(
                new RequestId($this->faker->uuid()),
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
}
