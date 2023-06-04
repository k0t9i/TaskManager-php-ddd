<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\PasswordHasherInterface as SymfonyPasswordHasherInterface;
use TaskManager\Shared\Infrastructure\Service\SymfonyPasswordHasher;

class SymfonyPasswordHasherTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testHashPassword(): void
    {
        $plainPassword = $this->faker->regexify('.{255}');
        $hashedPassword = $this->faker->regexify('.{255}');
        $symfonyHasher = $this->getMockBuilder(SymfonyPasswordHasherInterface::class)
            ->getMock();
        $symfonyHasher->method('hash')
            ->with($plainPassword)
            ->willReturn($hashedPassword);

        $hasher = new SymfonyPasswordHasher($symfonyHasher);

        $this->assertEquals($hashedPassword, $hasher->hashPassword($plainPassword));
    }

    public function testVerifyPassword(): void
    {
        $plainPassword = $this->faker->regexify('.{255}');
        $hashedPassword = $this->faker->regexify('.{255}');
        $symfonyHasher = $this->getMockBuilder(SymfonyPasswordHasherInterface::class)
            ->getMock();
        $symfonyHasher->method('verify')
            ->with($hashedPassword, $plainPassword)
            ->willReturn(true);

        $hasher = new SymfonyPasswordHasher($symfonyHasher);

        $this->assertTrue($hasher->verifyPassword($hashedPassword, $plainPassword));
    }
}
