<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Users\Domain\Event\UserWasCreatedEvent;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

class UserTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $id = new UserId($this->faker->uuid());
        $email = new UserEmail($this->faker->email());
        $profile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}'))
        );

        $user = User::create($id, $email, $profile);
        $events = $user->releaseEvents();

        $this->assertInstanceOf(User::class, $user);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserWasCreatedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals($id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'email' => $email->value,
            'firstname' => $profile->firstname->value,
            'lastname' => $profile->lastname->value,
            'password' => $profile->password->value,
        ], $events[0]->toPrimitives());
    }

    public function testRegisterAndReleaseEvent(): void
    {
        $id = new UserId($this->faker->uuid());
        $email = new UserEmail($this->faker->email());
        $profile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}'))
        );
        $user = new User($id, $email, $profile);
        $event = $this->getMockBuilder(DomainEventInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user->registerEvent($event);
        $events = $user->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertCount(0, $user->releaseEvents());
        $this->assertInstanceOf($event::class, $events[0]);
        $this->assertSame($event, $events[0]);
    }

    public function testChangeProfile(): void
    {
        $id = new UserId($this->faker->uuid());
        $email = new UserEmail($this->faker->email());
        $profile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}'))
        );
        $newProfile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}'))
        );
        $user = new User($id, $email, $profile);

        $user->changeProfile(
            $newProfile->firstname,
            $newProfile->lastname,
            $newProfile->password,
        );

        $events = $user->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserProfileWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals($id->value, $events[0]->getPerformerId());
        $this->assertEquals([
            'firstname' => $newProfile->firstname->value,
            'lastname' => $newProfile->lastname->value,
            'password' => $newProfile->password->value,
        ], $events[0]->toPrimitives());

        $user->changeProfile(
            null,
            null,
            null,
        );
        $events = $user->releaseEvents();
        $this->assertCount(0, $events);

        $user->changeProfile(
            $newProfile->firstname,
            $newProfile->lastname,
            $newProfile->password,
        );
        $events = $user->releaseEvents();
        $this->assertCount(0, $events);

        $user->changeProfile(
            $profile->firstname,
            null,
            $newProfile->password,
        );
        $events = $user->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertEquals([
            'firstname' => $profile->firstname->value,
            'lastname' => $newProfile->lastname->value,
            'password' => $newProfile->password->value,
        ], $events[0]->toPrimitives());
    }

    public function testEquals(): void
    {
        $id = new UserId($this->faker->uuid());
        $email = new UserEmail($this->faker->email());
        $profile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}'))
        );
        $user = new User($id, $email, $profile);
        $equalUser = new User($id, $email, $profile);
        $nonEqualUser = new User(
            new UserId($this->faker->uuid()),
            new UserEmail($this->faker->email()),
            new UserProfile(
                new UserFirstname($this->faker->regexify('.{255}')),
                new UserLastname($this->faker->regexify('.{255}')),
                new UserPassword($this->faker->regexify('.{255}'))
            )
        );
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($user->equals($user));
        $this->assertTrue($user->equals($equalUser));
        $this->assertFalse($user->equals($nonEqualUser));
        $this->assertFalse($user->equals($otherEquatable));
    }
}
