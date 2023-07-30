<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Handler;

use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;
use TaskManager\Users\Application\Command\UpdateProfileCommand;
use TaskManager\Users\Application\Service\UserSaverInterface;
use TaskManager\Users\Domain\Exception\PasswordAndRepeatPasswordDoNotMatchException;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;

final readonly class UpdateProfileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private UserSaverInterface $saver,
        private PasswordHasherInterface $passwordHasher,
        private AuthenticatorServiceInterface $authenticator,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(UpdateProfileCommand $command): void
    {
        $userId = new UserId($this->authenticator->getUserId());
        $user = $this->repository->findById($userId);
        if (null === $user) {
            throw new UserDoesNotExistException($userId->value);
        }

        if ($command->password !== $command->repeatPassword) {
            throw new PasswordAndRepeatPasswordDoNotMatchException();
        }

        $hashedPassword = $command->password ?
            new UserPassword($this->passwordHasher->hashPassword($command->password)) :
            null;
        $user->changeProfile(
            new UserFirstname($command->firstname),
            new UserLastname($command->lastname),
            $hashedPassword
        );

        $events = $user->releaseEvents();
        if (0 !== count($events)) {
            $newVersion = $this->saver->save($user, (int) $command->version);
            $this->eventBus->dispatch($events, $newVersion);
        }
    }
}
