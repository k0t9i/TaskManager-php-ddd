<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Handler;

use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Application\Service\PasswordHasherInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;
use TaskManager\Users\Application\Command\LoginCommand;
use TaskManager\Users\Domain\Repository\UserRepositoryInterface;
use TaskManager\Users\Domain\ValueObject\UserEmail;

final readonly class LoginCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface      $repository,
        private PasswordHasherInterface      $passwordHasher,
        private AuthenticatorServiceInterface $authenticator
    ) {
    }

    public function __invoke(LoginCommand $command): string
    {
        $user = $this->repository->findByEmail(new UserEmail($command->email));
        if (null === $user) {
            throw new UserDoesNotExistException($command->email);
        }

        if (!$this->passwordHasher->verifyPassword($user->getPassword()->value, $command->password)) {
            throw new UserDoesNotExistException($command->email);
        }

        return $this->authenticator->getToken($user->getId()->value);
    }
}
