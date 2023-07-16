<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Users\Domain\ValueObject\UserLastname;

final class UserLastnameTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreateWithValidValue(): void
    {
        $lastname = $this->faker->regexify('.{255}');

        $lastnameObject = new UserLastname($lastname);

        $this->assertEquals($lastname, $lastnameObject->value);
    }

    public function testCreateWithTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User lastname" should contain at most 255 characters.');

        new UserLastname($this->faker->regexify('.{256}'));
    }

    public function testCreateWithEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User lastname" cannot be blank.');

        new UserLastname('');
    }

    public function testToString(): void
    {
        $lastname = $this->faker->regexify('.{255}');

        $lastnameObject = new UserLastname($lastname);

        $this->assertEquals($lastname, (string) $lastnameObject);
    }

    public function testEquals(): void
    {
        $lastname = $this->faker->regexify('.{255}');
        $lastnameObject = new UserLastname($lastname);
        $equalLastname = new UserLastname($lastname);
        $nonEqualLastname = new UserLastname($this->faker->regexify('.{255}'));
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($lastnameObject->equals($lastnameObject));
        $this->assertTrue($lastnameObject->equals($equalLastname));
        $this->assertFalse($lastnameObject->equals($nonEqualLastname));
        $this->assertFalse($lastnameObject->equals($otherEquatable));
    }
}
