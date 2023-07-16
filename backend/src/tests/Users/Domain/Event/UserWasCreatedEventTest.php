<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\Event;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Users\Domain\Event\UserWasCreatedEvent;

class UserWasCreatedEventTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testToPrimitives(): void
    {
        $email = $this->faker->email();
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{255}');
        $expected = [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'password' => $password,
        ];

        $event = new UserWasCreatedEvent(
            $this->faker->regexify('.{255}'),
            $email,
            $firstname,
            $lastname,
            $password,
            $this->faker->regexify('.{255}'),
        );

        $this->assertEquals($expected, $event->toPrimitives());
    }

    public function testGetAggregateId(): void
    {
        $aggregateId = $this->faker->regexify('.{255}');
        $event = new UserWasCreatedEvent(
            $aggregateId,
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}')
        );

        $this->assertEquals($aggregateId, $event->getAggregateId());
    }

    public function testFromPrimitives(): void
    {
        $aggregateId = $this->faker->regexify('.{255}');
        $email = $this->faker->email();
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{255}');
        $occurredOn = $this->faker->regexify('.{255}');
        $performerId = $this->faker->regexify('.{255}');
        $primitives = [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'password' => $password,
        ];
        $expectedEvent = new UserWasCreatedEvent(
            $aggregateId,
            $email,
            $firstname,
            $lastname,
            $password,
            $performerId,
            $occurredOn
        );

        $this->assertEquals(
            $expectedEvent,
            UserWasCreatedEvent::fromPrimitives($aggregateId, $primitives, $performerId, $occurredOn)
        );
    }

    public function testGetOccurredOn(): void
    {
        $occurredOn = $this->faker->regexify('.{255}');
        $event = new UserWasCreatedEvent(
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $occurredOn
        );

        $this->assertEquals($occurredOn, $event->getOccurredOn());
    }

    public function testGetPerformerId(): void
    {
        $performerId = $this->faker->regexify('.{255}');
        $event = new UserWasCreatedEvent(
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $performerId
        );

        $this->assertEquals($performerId, $event->getPerformerId());
    }
}
