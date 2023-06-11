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
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RejectedRequestStatus;
use TaskManager\Projects\Domain\ValueObject\RequestChangeDate;
use TaskManager\Projects\Domain\ValueObject\RequestId;

class RequestTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate()
    {
        $id = new RequestId($this->faker->uuid());
        $userId = new ProjectUserId($this->faker->uuid());

        $request = Request::create($id, $userId);

        $this->assertEquals($id, $request->getId());
        $this->assertEquals($userId, $request->getUserId());
        $this->assertInstanceOf(PendingRequestStatus::class, $request->getStatus());
    }

    public function testIsPendingForUser()
    {
        $userId = new ProjectUserId($this->faker->uuid());
        $pendingRequest = new Request(
            new RequestId($this->faker->uuid()),
            $userId,
            new PendingRequestStatus(),
            new RequestChangeDate()
        );
        $rejectedRequest = new Request(
            new RequestId($this->faker->uuid()),
            $userId,
            new RejectedRequestStatus(),
            new RequestChangeDate()
        );

        $this->assertTrue($pendingRequest->isPendingForUser($userId));
        $this->assertFalse($pendingRequest->isPendingForUser(new ProjectUserId($this->faker->uuid())));
        $this->assertFalse($rejectedRequest->isPendingForUser($userId));
    }

    public function testConfirmRequest()
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectUserId($this->faker->uuid()),
            new PendingRequestStatus(),
            new RequestChangeDate()
        );

        $request->changeStatus(new ConfirmedRequestStatus());

        $this->assertInstanceOf(ConfirmedRequestStatus::class, $request->getStatus());
    }

    public function testRejectRequest()
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectUserId($this->faker->uuid()),
            new PendingRequestStatus(),
            new RequestChangeDate()
        );

        $request->changeStatus(new RejectedRequestStatus());

        $this->assertInstanceOf(RejectedRequestStatus::class, $request->getStatus());
    }

    public function testChangeToInvalidStatus()
    {
        $request = new Request(
            new RequestId($this->faker->uuid()),
            new ProjectUserId($this->faker->uuid()),
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
