<?php

declare(strict_types=1);

namespace TaskManager\Tests\Projects\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\Exception\InvalidProjectRequestStatusTransitionException;
use TaskManager\Projects\Domain\ValueObject\ConfirmedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\PendingRequestStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestChangeDate;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Shared\Domain\ValueObject\UserId;

class RequestTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $id = new RequestId($this->faker->uuid());
        $projectId = new ProjectId($this->faker->uuid());
        $userId = new UserId($this->faker->uuid());

        $request = Request::create($id, $projectId, $userId);

        $this->assertEquals($id, $request->getId());
        $this->assertEquals($projectId, $request->getProjectId());
        $this->assertEquals($userId, $request->getUserId());
        $this->assertInstanceOf(PendingRequestStatus::class, $request->getStatus());
    }

    public function testIsPendingForUser(): void
    {
        $userId = new UserId($this->faker->uuid());
        $pendingRequest = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectId($this->faker->uuid()),
            $userId,
            new PendingRequestStatus(),
            new RequestChangeDate()
        );
        $rejectedRequest = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectId($this->faker->uuid()),
            $userId,
            new RejectedRequestStatus(),
            new RequestChangeDate()
        );

        $this->assertTrue($pendingRequest->isPendingForUser($userId));
        $this->assertFalse($pendingRequest->isPendingForUser(new UserId($this->faker->uuid())));
        $this->assertFalse($rejectedRequest->isPendingForUser($userId));
    }

    public function testConfirmRequest(): void
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectId($this->faker->uuid()),
            new UserId($this->faker->uuid()),
            new PendingRequestStatus(),
            new RequestChangeDate()
        );

        $request->changeStatus(new ConfirmedRequestStatus());

        $this->assertInstanceOf(ConfirmedRequestStatus::class, $request->getStatus());
    }

    public function testRejectRequest(): void
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectId($this->faker->uuid()),
            new UserId($this->faker->uuid()),
            new PendingRequestStatus(),
            new RequestChangeDate()
        );

        $request->changeStatus(new RejectedRequestStatus());

        $this->assertInstanceOf(RejectedRequestStatus::class, $request->getStatus());
    }

    public function testChangeToInvalidStatus(): void
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectId($this->faker->uuid()),
            new UserId($this->faker->uuid()),
            new ConfirmedRequestStatus(),
            new RequestChangeDate()
        );

        $this->expectException(InvalidProjectRequestStatusTransitionException::class);
        $this->expectExceptionMessage(sprintf(
            'Project request status "%s" cannot be changed to "%s"',
            ConfirmedRequestStatus::class,
            PendingRequestStatus::class
        ));

        $request->changeStatus(new PendingRequestStatus());
    }
}
