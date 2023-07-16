<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

final class UserProfileTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{400}');
        $firstnameObject = new UserFirstname($firstname);
        $lastnameObject = new UserLastname($lastname);
        $passwordObject = new UserPassword($password);

        $profile = new UserProfile(
            $firstnameObject,
            $lastnameObject,
            $passwordObject
        );

        $this->assertSame($firstnameObject, $profile->firstname);
        $this->assertSame($lastnameObject, $profile->lastname);
        $this->assertSame($passwordObject, $profile->password);
    }

    public function testEquals(): void
    {
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{400}');
        $profile = new UserProfile(
            new UserFirstname($firstname),
            new UserLastname($lastname),
            new UserPassword($password),
        );
        $equalProfile = new UserProfile(
            new UserFirstname($firstname),
            new UserLastname($lastname),
            new UserPassword($password),
        );
        $otherFirstname = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($lastname),
            new UserPassword($password),
        );
        $otherLastname = new UserProfile(
            new UserFirstname($firstname),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($password),
        );
        $otherPassword = new UserProfile(
            new UserFirstname($firstname),
            new UserLastname($lastname),
            new UserPassword($this->faker->regexify('.{255}')),
        );
        $nonEqualProfile = new UserProfile(
            new UserFirstname($this->faker->regexify('.{255}')),
            new UserLastname($this->faker->regexify('.{255}')),
            new UserPassword($this->faker->regexify('.{255}')),
        );
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($profile->equals($profile));
        $this->assertTrue($profile->equals($equalProfile));
        $this->assertFalse($profile->equals($otherFirstname));
        $this->assertFalse($profile->equals($otherLastname));
        $this->assertFalse($profile->equals($otherPassword));
        $this->assertFalse($profile->equals($nonEqualProfile));
        $this->assertFalse($profile->equals($otherEquatable));
    }
}
