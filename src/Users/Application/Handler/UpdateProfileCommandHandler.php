<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Handler;

use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Users\Application\Command\UpdateProfileCommand;
use TaskManager\Users\Domain\Exception\PasswordAndRepeatPasswordDoNotMatchException;
use TaskManager\Users\Domain\Exception\UserNotExistException;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;

final readonly class UpdateProfileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface       $repository,
        private PasswordHasherInterface       $passwordHasher,
        private AuthenticatorServiceInterface $authenticator,
        private IntegrationEventBusInterface  $eventBus
    ) {
    }

    public function __invoke(UpdateProfileCommand $command): void
    {
        $userId = new UserId($this->authenticator->getUserId());
        $user = $this->repository->findById($userId);
        if (null === $user) {
            throw new UserNotExistException($userId->value);
        }

        if ($command->password !== $command->repeatPassword) {
            throw new PasswordAndRepeatPasswordDoNotMatchException();
        }

        $hashedPassword = $this->passwordHasher->hashPassword($command->password);
        $user->changeProfile(
            $command->firstname ? new UserFirstname($command->firstname) : null,
            $command->lastname ? new UserLastname($command->lastname) : null,
            $command->password ? new UserPassword($hashedPassword) : null
        );

        $this->repository->save($user);
        $this->eventBus->dispatch(...$user->releaseEvents());
    }
}