<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Users\Domain\ValueObject\UserPassword;

final class UserPasswordTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreateWithValidValue(): void
    {
        $password = $this->faker->regexify('.{255}');

        $passwordObject = new UserPassword($password);

        $this->assertEquals($password, $passwordObject->value);
    }

    public function testCreateWithEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Password" cannot be blank.');

        new UserPassword('');
    }

    public function testToString(): void
    {
        $password = $this->faker->regexify('.{255}');

        $passwordObject = new UserPassword($password);

        $this->assertEquals($password, (string) $passwordObject);
    }

    public function testEquals(): void
    {
        $password = $this->faker->regexify('.{255}');
        $passwordObject = new UserPassword($password);
        $equalPassword = new UserPassword($password);
        $nonEqualPassword = new UserPassword($this->faker->regexify('.{255}'));
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($passwordObject->equals($passwordObject));
        $this->assertTrue($passwordObject->equals($equalPassword));
        $this->assertFalse($passwordObject->equals($nonEqualPassword));
        $this->assertFalse($passwordObject->equals($otherEquatable));
    }
}
