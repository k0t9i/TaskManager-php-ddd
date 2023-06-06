<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;
use TaskManager\Users\Application\Command\LoginCommand;
use TaskManager\Users\Application\Command\RegisterCommand;
use TaskManager\Users\Infrastructure\Service\DTO\UserLoginDTO;
use TaskManager\Users\Infrastructure\Service\DTO\UserRegisterDTO;

#[AsController]
#[Route('/api/security', name: 'security.')]
final readonly class SecurityController
{
    public function __construct(
        private CommandBusInterface    $commandBus,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    #[Route('/register/', name: 'register', methods: ['POST'])]
    public function register(UserRegisterDTO $dto): JsonResponse
    {
        $command = new RegisterCommand(
            $this->uuidGenerator->generate(),
            $dto->email,
            $dto->firstname,
            $dto->lastname,
            $dto->password,
            $dto->repeatPassword
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }

    #[Route('/login/', name: 'login', methods: ['POST'])]
    public function login(UserLoginDTO $dto): JsonResponse
    {
        $command = new LoginCommand(
            $dto->email,
            $dto->password
        );

        $token = $this->commandBus->dispatch($command);

        return new JsonResponse([
            'token' => $token,
        ]);
    }
}
