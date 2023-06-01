<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

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

        self::assertEquals($email, $emailObject->value);
    }

    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Email" is not a valid email address.');

        new Email($this->faker->regexify('.{255}'));
    }
}
