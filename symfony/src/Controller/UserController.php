<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Users\Application\Command\UpdateProfileCommand;
use TaskManager\Users\Infrastructure\Service\DTO\UserProfileUpdateDTO;

#[AsController]
#[Route('/api/users', name: 'user.')]
final readonly class UserController
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    #[Route('/profile/', name: 'updateProfile', methods: ['PATCH'])]
    public function updateProfile(UserProfileUpdateDTO $dto): JsonResponse
    {
        $command = new UpdateProfileCommand(
            $dto->firstname,
            $dto->lastname,
            $dto->password,
            $dto->repeatPassword
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }
}
