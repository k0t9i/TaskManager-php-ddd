<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Users\Domain\ValueObject\UserEmail;

final class UserEmailTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreateWithValidValue(): void
    {
        $email = $this->faker->email();

        $emailObject = new UserEmail($email);

        $this->assertEquals($email, $emailObject->value);
    }

    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User email" is not a valid email address.');

        new UserEmail($this->faker->regexify('.{255}'));
    }

    public function testCreateWithEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User email" cannot be blank.');

        new UserEmail('');
    }

    public function testToString(): void
    {
        $email = $this->faker->email();

        $emailObject = new UserEmail($email);

        $this->assertEquals($email, (string) $emailObject);
    }

    public function testEquals(): void
    {
        $email = $this->faker->email();
        $emailObject = new UserEmail($email);
        $equalEmail = new UserEmail($email);
        $nonEqualEmail = new UserEmail($this->faker->email());
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($emailObject->equals($emailObject));
        $this->assertTrue($emailObject->equals($equalEmail));
        $this->assertFalse($emailObject->equals($nonEqualEmail));
        $this->assertFalse($emailObject->equals($otherEquatable));
    }
}
