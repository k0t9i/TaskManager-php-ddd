<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
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
#[Areas(['default'])]
final readonly class SecurityController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    #[Route('/register/', name: 'register', methods: ['POST'])]
    #[OA\Post(
        description: 'Registration',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: UserRegisterDTO::class)
            )
        ),
        tags: [
            'security',
        ],
        responses: [
            new OA\Response(ref: '#components/responses/createObject', response: '201'),
            new OA\Response(ref: '#components/responses/generic401', response: '403'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
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
    #[OA\Post(
        description: 'Sign in',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: UserLoginDTO::class)
            )
        ),
        tags: [
            'security',
        ],
        responses: [
            new OA\Response(ref: '#components/responses/login', response: '200'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
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
