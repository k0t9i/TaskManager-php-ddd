<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Handler;

use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Users\Application\Command\RegisterCommand;
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

final readonly class RegisterCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface      $repository,
        private PasswordHasherInterface      $passwordHasher,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(RegisterCommand $command): void
    {
        $user = $this->repository->findByEmail(new UserEmail($command->email));
        if (null !== $user) {
            throw new EmailIsAlreadyTakenException($command->email);
        }

        if ($command->password !== $command->repeatPassword) {
            throw new PasswordAndRepeatPasswordDoNotMatchException();
        }

        $newUser = User::create(
            new UserId($command->id),
            new UserEmail($command->email),
            new UserProfile(
                new UserFirstname($command->firstname),
                new UserLastname($command->lastname),
                new UserPassword($this->passwordHasher->hashPassword($command->password))
            )
        );

        $this->repository->save($newUser);
        $this->eventBus->dispatch(...$newUser->releaseEvents());
    }
}
