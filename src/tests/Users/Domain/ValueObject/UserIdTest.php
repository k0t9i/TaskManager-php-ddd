<?php

declare(strict_types=1);

namespace TaskManager\Test\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Users\Domain\ValueObject\UserId;

class UserIdTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreateWithValidValue(): void
    {
        $uuid = $this->faker->uuid();

        $uuidObject = new UserId($uuid);

        $this->assertEquals($uuid, $uuidObject->value);
    }

    public function testCreateWithInvalidValue(): void
    {
        $invalidValue = $this->faker->regexify('.{255}');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid uuid "%s"', $invalidValue));

        new UserId($invalidValue);
    }

    public function testToString()
    {
        $uuid = $this->faker->uuid();

        $uuidObject = new UserId($uuid);

        $this->assertEquals($uuid, (string) $uuidObject);
    }

    public function testCreateFrom()
    {
        $uuid = $this->faker->uuid();
        $otherUuidObject = new UserId($uuid);

        $uuidObject = UserId::createFrom($otherUuidObject);

        $this->assertNotSame($otherUuidObject, $uuidObject);
        $this->assertEquals($uuidObject->value, $otherUuidObject->value);
    }
}
