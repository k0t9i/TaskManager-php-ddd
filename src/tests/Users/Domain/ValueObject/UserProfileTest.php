<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Domain\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
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
}
