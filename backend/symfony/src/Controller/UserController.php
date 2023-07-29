<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Users\Application\Command\UpdateProfileCommand;
use TaskManager\Users\Infrastructure\Service\DTO\UserProfileUpdateDTO;

#[AsController]
#[Route('/api/users', name: 'user.')]
#[Areas(['default'])]
final readonly class UserController
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    #[Route('/', name: 'updateProfile', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Update profile',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: UserProfileUpdateDTO::class)
            )
        ),
        tags: [
            'user',
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function updateProfile(UserProfileUpdateDTO $dto): JsonResponse
    {
        $command = new UpdateProfileCommand(
            $dto->firstname,
            $dto->lastname,
            $dto->password,
            $dto->repeatPassword,
            $dto->version
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }
}
