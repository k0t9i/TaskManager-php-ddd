<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Application\Handler;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;
use TaskManager\Shared\Domain\ValueObject\UserId;
use TaskManager\Users\Application\Command\LoginCommand;
use TaskManager\Users\Application\Handler\LoginCommandHandler;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

class LoginCommandHandlerTest extends TestCase
{
    private Generator $faker;

    private LoginCommand $command;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->command = new LoginCommand(
            $this->faker->email(),
            $this->faker->regexify('.{255}'),
        );

        $this->user = new User(
            new UserId($this->faker->uuid()),
            new UserEmail($this->command->email),
            new UserProfile(
                new UserFirstname($this->faker->regexify('.{255}')),
                new UserLastname($this->faker->regexify('.{255}')),
                new UserPassword($this->command->password)
            )
        );
    }

    public function testUserNotFound(): void
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findByEmail')
            ->willReturn(null);
        $handler = new LoginCommandHandler(
            $repository,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $this->getMockBuilder(AuthenticatorServiceInterface::class)->getMock(),
        );

        $this->expectException(UserDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" doesn\'t exist',
            $this->command->email
        ));

        $handler($this->command);
    }

    public function testPasswordAndRepeatPasswordDoNotMatch(): void
    {
        $hasher = $this->getMockBuilder(PasswordHasherInterface::class)
            ->getMock();
        $hasher->method('verifyPassword')
            ->willReturn(false);
        $handler = new LoginCommandHandler(
            $this->getMockBuilder(UserRepositoryInterface::class)->getMock(),
            $hasher,
            $this->getMockBuilder(AuthenticatorServiceInterface::class)->getMock(),
        );

        $this->expectException(UserDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" doesn\'t exist',
            $this->command->email
        ));

        $handler($this->command);
    }

    public function testValidRun(): void
    {
        $token = $this->faker->regexify('.{255}');
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->expects(self::once())
            ->method('findByEmail')
            ->willReturn($this->user);
        $hasher = $this->getMockBuilder(PasswordHasherInterface::class)
            ->getMock();
        $hasher->expects(self::once())
            ->method('verifyPassword')
            ->with($this->user->getPassword()->value, $this->command->password)
            ->willReturn(true);
        $authenticator = $this->getMockBuilder(AuthenticatorServiceInterface::class)
            ->getMock();
        $authenticator->expects(self::once())
            ->method('getToken')
            ->with($this->user->getId()->value)
            ->willReturn($token);
        $handler = new LoginCommandHandler(
            $repository,
            $hasher,
            $authenticator,
        );

        $this->assertEquals($token, $handler($this->command));
    }
}
