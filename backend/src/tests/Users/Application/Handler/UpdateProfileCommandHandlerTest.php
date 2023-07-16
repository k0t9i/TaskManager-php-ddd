<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Application\Handler;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;
use TaskManager\Users\Application\Command\UpdateProfileCommand;
use TaskManager\Users\Application\Handler\UpdateProfileCommandHandler;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Exception\PasswordAndRepeatPasswordDoNotMatchException;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

class UpdateProfileCommandHandlerTest extends TestCase
{
    private Generator $faker;

    private User $user;

    private UpdateProfileCommand $command;

    private UpdateProfileCommand $repeatPasswordCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $password = $this->faker->regexify('.{255}');
        $this->command = new UpdateProfileCommand(
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $password,
            $password
        );

        $this->repeatPasswordCommand = new UpdateProfileCommand(
            $this->command->firstname,
            $this->command->lastname,
            $this->command->password,
            $this->faker->regexify('.{255}')
        );

        $this->user = new User(
            new UserId($this->faker->uuid()),
            new UserEmail($this->faker->email()),
            new UserProfile(
                new UserFirstname($this->faker->regexify('.{255}')),
                new UserLastname($this->faker->regexify('.{255}')),
                new UserPassword($this->faker->regexify('.{255}'))
            )
        );
    }

    public function testUserNotFound(): void
    {
        $id = $this->faker->uuid();
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findById')
            ->willReturn(null);
        $authenticator = $this->getMockBuilder(AuthenticatorServiceInterface::class)
            ->getMock();
        $authenticator->method('getUserId')
            ->willReturn($id);
        $handler = new UpdateProfileCommandHandler(
            $repository,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $authenticator,
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock(),
        );

        $this->expectException(UserDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'User "%s" doesn\'t exist',
            $id
        ));
        $handler($this->command);
    }

    public function testPasswordAndRepeatPasswordDoNotMatch(): void
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findById')
            ->willReturn($this->user);
        $authenticator = $this->getMockBuilder(AuthenticatorServiceInterface::class)
            ->getMock();
        $authenticator->method('getUserId')
            ->willReturn($this->faker->uuid());
        $handler = new UpdateProfileCommandHandler(
            $repository,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $authenticator,
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock(),
        );

        $this->expectException(PasswordAndRepeatPasswordDoNotMatchException::class);
        $handler($this->repeatPasswordCommand);
    }

    public function testValidRun(): void
    {
        $id = $this->faker->uuid();
        $userId = new UserId($id);
        $hashedPassword = $this->faker->regexify('.{255}');
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);
        $repository->expects(self::once())
            ->method('save');
        $hasher = $this->getMockBuilder(PasswordHasherInterface::class)
            ->getMock();
        $hasher->expects(self::once())
            ->method('hashPassword')
            ->with($this->command->password)
            ->willReturn($hashedPassword);
        $authenticator = $this->getMockBuilder(AuthenticatorServiceInterface::class)
            ->getMock();
        $authenticator->expects(self::once())
            ->method('getUserId')
            ->willReturn($id);
        $eventBus = $this->getMockBuilder(IntegrationEventBusInterface::class)
            ->getMock();
        $eventBus->expects(self::once())
            ->method('dispatch');
        $handler = new UpdateProfileCommandHandler(
            $repository,
            $hasher,
            $authenticator,
            $eventBus,
        );

        $handler($this->command);
    }
}
