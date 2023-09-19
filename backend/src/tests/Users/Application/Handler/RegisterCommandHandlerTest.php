<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Application\Handler;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Shared\Domain\ValueObject\UserId;
use TaskManager\Users\Application\Command\RegisterCommand;
use TaskManager\Users\Application\Handler\RegisterCommandHandler;
use TaskManager\Users\Application\Service\UserSaverInterface;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\Exception\EmailIsAlreadyTakenException;
use TaskManager\Users\Domain\Exception\PasswordAndRepeatPasswordDoNotMatchException;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;

class RegisterCommandHandlerTest extends TestCase
{
    private User $user;

    private RegisterCommand $command;

    private RegisterCommand $repeatPasswordCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $faker = Factory::create();

        $password = $faker->regexify('.{255}');
        $this->command = new RegisterCommand(
            $faker->uuid(),
            $faker->email(),
            $faker->regexify('.{255}'),
            $faker->regexify('.{255}'),
            $password,
            $password
        );

        $this->repeatPasswordCommand = new RegisterCommand(
            $this->command->id,
            $this->command->email,
            $this->command->firstname,
            $this->command->lastname,
            $this->command->password,
            $faker->regexify('.{255}')
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

    public function testEmailIsAlreadyTaken(): void
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findByEmail')
            ->willReturn($this->user);
        $saver = $this->getMockBuilder(UserSaverInterface::class)
            ->getMock();
        $saver->expects(self::never())
            ->method('save');

        $this->expectException(EmailIsAlreadyTakenException::class);
        $this->expectExceptionMessage(sprintf(
            'Email "%s" is already taken',
            $this->repeatPasswordCommand->email
        ));

        (new RegisterCommandHandler(
            $repository,
            $saver,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock()
        ))($this->repeatPasswordCommand);
    }

    public function testPasswordAndRepeatPasswordDoNotMatch(): void
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->method('findByEmail')
            ->willReturn(null);
        $saver = $this->getMockBuilder(UserSaverInterface::class)
            ->getMock();
        $saver->expects(self::never())
            ->method('save');

        $this->expectException(PasswordAndRepeatPasswordDoNotMatchException::class);

        (new RegisterCommandHandler(
            $repository,
            $saver,
            $this->getMockBuilder(PasswordHasherInterface::class)->getMock(),
            $this->getMockBuilder(IntegrationEventBusInterface::class)->getMock()
        ))($this->repeatPasswordCommand);
    }

    public function testValidRun(): void
    {
        $repository = $this->getMockBuilder(UserRepositoryInterface::class)
            ->getMock();
        $repository->expects(self::once())
            ->method('findByEmail')
            ->willReturn(null);
        $saver = $this->getMockBuilder(UserSaverInterface::class)
            ->getMock();
        $saver->expects(self::once())
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
            $saver,
            $hasher,
            $eventBus
        ))($this->command);
    }
}
