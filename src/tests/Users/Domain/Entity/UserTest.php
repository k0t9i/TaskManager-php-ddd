<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\Entity;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Event\DomainEventInterface;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Event\UserProfileWasChangedEvent;
use TaskManager\Users\Domain\Event\UserWasCreatedDomainEvent;
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
        $this->assertInstanceOf(UserWasCreatedDomainEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'email' => $email->value,
            'firstname' => $profile->firstname->value,
            'lastname' => $profile->lastname->value,
            'password' => $profile->password->value
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

        $user->changeProfile($newProfile);

        $events = $user->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserProfileWasChangedEvent::class, $events[0]);
        $this->assertEquals($id->value, $events[0]->getAggregateId());
        $this->assertEquals([
            'firstname' => $newProfile->firstname->value,
            'lastname' => $newProfile->lastname->value,
            'password' => $newProfile->password->value
        ], $events[0]->toPrimitives());
    }
}
