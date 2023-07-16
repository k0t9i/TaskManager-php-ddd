<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Users\Domain\ValueObject\UserFirstname;

final class UserFirstnameTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreateWithValidValue(): void
    {
        $firstname = $this->faker->regexify('.{255}');

        $firstnameObject = new UserFirstname($firstname);

        $this->assertEquals($firstname, $firstnameObject->value);
    }

    public function testCreateWithTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User firstname" should contain at most 255 characters.');

        new UserFirstname($this->faker->regexify('.{256}'));
    }

    public function testCreateWithEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"User firstname" cannot be blank.');

        new UserFirstname('');
    }

    public function testToString(): void
    {
        $firstname = $this->faker->regexify('.{255}');

        $firstnameObject = new UserFirstname($firstname);

        $this->assertEquals($firstname, (string) $firstnameObject);
    }

    public function testEquals(): void
    {
        $firstname = $this->faker->regexify('.{255}');
        $firstnameObject = new UserFirstname($firstname);
        $equalFirstname = new UserFirstname($firstname);
        $nonEqualFirstname = new UserFirstname($this->faker->regexify('.{255}'));
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($firstnameObject->equals($firstnameObject));
        $this->assertTrue($firstnameObject->equals($equalFirstname));
        $this->assertFalse($firstnameObject->equals($nonEqualFirstname));
        $this->assertFalse($firstnameObject->equals($otherEquatable));
    }
}
