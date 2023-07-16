<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\ValueObject;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Infrastructure\ValueObject\SymfonyUser;

class SymfonyUserTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testGetUserIdentifier(): void
    {
        $id = $this->faker->regexify('.{255}');

        $user = new SymfonyUser($id);

        $this->assertEquals($user->getUserIdentifier(), $id);
    }
}
