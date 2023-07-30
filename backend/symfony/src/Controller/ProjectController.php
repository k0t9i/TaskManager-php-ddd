<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Projects\Application\Command\ActivateProjectCommand;
use TaskManager\Projects\Application\Command\ChangeProjectInformationCommand;
use TaskManager\Projects\Application\Command\ChangeProjectOwnerCommand;
use TaskManager\Projects\Application\Command\CloseProjectCommand;
use TaskManager\Projects\Application\Command\ConfirmRequestCommand;
use TaskManager\Projects\Application\Command\CreateProjectCommand;
use TaskManager\Projects\Application\Command\CreateRequestCommand;
use TaskManager\Projects\Application\Command\CreateTaskCommand;
use TaskManager\Projects\Application\Command\LeaveCommand;
use TaskManager\Projects\Application\Command\RejectRequestCommand;
use TaskManager\Projects\Application\Command\RemoveParticipantCommand;
use TaskManager\Projects\Infrastructure\Service\DTO\ProjectInformationDTO;
use TaskManager\Projects\Infrastructure\Service\DTO\TaskInformationDTO;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;

#[AsController]
#[Route('/api/projects', name: 'project.')]
#[Areas(['default'])]
final readonly class ProjectController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    #[OA\Post(
        description: 'Create a new project',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: ProjectInformationDTO::class, groups: ['create'])
            )
        ),
        tags: [
            'project',
        ],
        responses: [
            new OA\Response(ref: '#components/responses/createObject', response: '201'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function create(ProjectInformationDTO $dto): JsonResponse
    {
        $command = new CreateProjectCommand(
            $this->uuidGenerator->generate(),
            $dto->name,
            $dto->description,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }

    #[Route('/{id}/', name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Update project information',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: ProjectInformationDTO::class, groups: ['update']),
            )
        ),
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/version', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function update(string $id, ProjectInformationDTO $dto): JsonResponse
    {
        $command = new ChangeProjectInformationCommand(
            $id,
            $dto->name,
            $dto->description,
            $dto->finishDate,
            $dto->version
        );

        $version = $this->commandBus->dispatch($command);

        return new JsonResponse([
            'version' => $version,
        ]);
    }

    #[Route('/{id}/activate/', name: 'activate', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Activate closed project',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function activate(string $id): JsonResponse
    {
        $command = new ActivateProjectCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/close/', name: 'close', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Close active project',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function close(string $id): JsonResponse
    {
        $command = new CloseProjectCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/change-owner/{ownerId}/', name: 'changeOwner', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Change project owner',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
            new OA\Parameter(
                name: 'ownerId',
                description: 'New owner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(ref: '#/components/schemas/objectId/properties/id')
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function changeOwner(string $id, string $ownerId): JsonResponse
    {
        $command = new ChangeProjectOwnerCommand($id, $ownerId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/participants/{participantId}/', name: 'removeParticipant', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Remove project participant',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
            new OA\Parameter(
                name: 'participantId',
                description: 'ID of participant to be removed',
                in: 'path',
                required: true,
                schema: new OA\Schema(ref: '#/components/schemas/objectId/properties/id')
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function removeParticipant(string $id, string $participantId): JsonResponse
    {
        $command = new RemoveParticipantCommand($id, $participantId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/leave/', name: 'leave', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Leave project',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function leave(string $id): JsonResponse
    {
        $command = new LeaveCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/requests/', name: 'createRequest', methods: ['POST'])]
    #[OA\Post(
        description: 'Create request to the project',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/createObject', response: '201'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function createRequest(string $id): JsonResponse
    {
        $command = new CreateRequestCommand(
            $this->uuidGenerator->generate(),
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }

    #[Route('/{id}/requests/{requestId}/confirm/', name: 'confirmRequest', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Confirm pending request',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
            new OA\Parameter(
                ref: '#/components/parameters/requestId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function confirmRequest(string $id, string $requestId): JsonResponse
    {
        $command = new ConfirmRequestCommand(
            $requestId,
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/requests/{requestId}/reject/', name: 'rejectRequest', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Reject pending request',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
            new OA\Parameter(
                ref: '#/components/parameters/requestId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function rejectRequest(string $id, string $requestId): JsonResponse
    {
        $command = new RejectRequestCommand(
            $requestId,
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/tasks/', name: 'createTask', methods: ['POST'])]
    #[OA\Post(
        description: 'Create a new task',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: TaskInformationDTO::class, groups: ['create'])
            )
        ),
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/createObject', response: '201'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function createTask(string $id, TaskInformationDTO $dto): JsonResponse
    {
        $command = new CreateTaskCommand(
            $this->uuidGenerator->generate(),
            $id,
            $dto->name,
            $dto->brief,
            $dto->description,
            $dto->startDate,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }
}
