<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Application\Handler;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Users\Application\Command\RegisterCommand;
use TaskManager\Users\Application\Handler\RegisterCommandHandler;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Exception\EmailIsAlreadyTakenException;
use TaskManager\Users\Domain\Exception\PasswordAndRepeatPasswordDoNotMatchException;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

class RegisterCommandHandlerTest extends TestCase
{
    private Generator $faker;

    private User $user;

    private RegisterCommand $command;

    private RegisterCommand $repeatPasswordCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $password = $this->faker->regexify('.{255}');
        $this->command = new RegisterCommand(
            $this->faker->uuid(),
            $this->faker->email(),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $password,
            $password
        );

        $this->repeatPasswordCommand = new RegisterCommand(
            $this->command->id,
            $this->command->email,
            $this->command->firstname,
            $this->command->lastname,
            $this->command->password,
            $this->faker->regexify('.{255}')
        );

        $this->user = new User(
            new UserId($this->repeatPasswordCommand->id),
            new UserEmail($this->repeatPasswordCommand->email),
            new UserProfile(
                new UserFirstname($this->repeatPasswordCommand->firstname),
                new UserLastname($this->repeatPasswordCommand->lastname),
                new UserPassword($this->repeatPasswordCommand->password)
            )
        );
    }

    public function testEmailIsAlreadyTaken()
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findByEmail')
            ->willReturn($this->user);

        $this->expectException(EmailIsAlreadyTakenException::class);
        $this->expectExceptionMessage(sprintf(
            'Email "%s" is already taken',
            $this->repeatPasswordCommand->email
        ));

        (new RegisterCommandHandler(
            $repository,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock()
        ))($this->repeatPasswordCommand);
    }

    public function testPasswordAndRepeatPasswordDoNotMatch()
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findByEmail')
            ->willReturn(null);

        $this->expectException(PasswordAndRepeatPasswordDoNotMatchException::class);

        (new RegisterCommandHandler(
            $repository,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock()
        ))($this->repeatPasswordCommand);
    }

    public function testValidRun()
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->expects(self::once())
            ->method('findByEmail')
            ->willReturn(null);
        $repository->expects(self::once())
            ->method('save');
        $hasher = $this->getMockBuilder(PasswordHasherInterface::class)
            ->getMock();
        $hasher->expects(self::once())
            ->method('hashPassword')
            ->with($this->command->password)
            ->willReturn($this->command->password);
        $eventBus = $this->getMockBuilder(IntegrationEventBusInterface::class)
            ->getMock();
        $eventBus->expects(self::once())
            ->method('dispatch');

        (new RegisterCommandHandler(
            $repository,
            $hasher,
            $eventBus
        ))($this->command);
    }
}
