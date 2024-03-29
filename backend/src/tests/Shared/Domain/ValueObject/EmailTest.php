<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Shared\Domain\ValueObject\Email;

final class EmailTest extends TestCase
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

        $emailObject = new Email($email);

        $this->assertEquals($email, $emailObject->value);
    }

    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Email" is not a valid email address.');

        new Email($this->faker->regexify('.{255}'));
    }

    public function testToString(): void
    {
        $email = $this->faker->email();

        $emailObject = new Email($email);

        $this->assertEquals($email, (string) $emailObject);
    }

    public function testEquals(): void
    {
        $email = $this->faker->email();
        $emailObject = new Email($email);
        $equalEmail = new Email($email);
        $nonEqualEmail = new Email($this->faker->email());
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($emailObject->equals($emailObject));
        $this->assertTrue($emailObject->equals($equalEmail));
        $this->assertFalse($emailObject->equals($nonEqualEmail));
        $this->assertFalse($emailObject->equals($otherEquatable));
    }
}
