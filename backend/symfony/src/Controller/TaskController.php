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
use TaskManager\Projects\Application\Command\ActivateTaskCommand;
use TaskManager\Projects\Application\Command\ChangeTaskInformationCommand;
use TaskManager\Projects\Application\Command\CloseTaskCommand;
use TaskManager\Projects\Application\Command\CreateTaskLinkCommand;
use TaskManager\Projects\Application\Command\DeleteTaskLinkCommand;
use TaskManager\Projects\Infrastructure\Service\DTO\TaskInformationDTO;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;

#[AsController]
#[Route('/api/tasks', name: 'task.')]
#[Areas(['default'])]
final readonly class TaskController
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    #[Route('/{id}/', name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Update task information',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: TaskInformationDTO::class, groups: ['update']),
            )
        ),
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
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
    public function update(string $id, TaskInformationDTO $dto): JsonResponse
    {
        $command = new ChangeTaskInformationCommand(
            $id,
            $dto->name,
            $dto->brief,
            $dto->description,
            $dto->startDate,
            $dto->finishDate,
            $dto->version
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/activate/', name: 'activate', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Activate closed task',
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
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
        $command = new ActivateTaskCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/close/', name: 'close', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Close active task',
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
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
        $command = new CloseTaskCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/links/{linkedTaskId}/', name: 'createLink', methods: ['POST'])]
    #[OA\Post(
        description: 'Create link to another task',
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
            ),
            new OA\Parameter(
                ref: '#/components/parameters/linkedTaskId'
            ),
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic201', response: '201'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
            new OA\Response(ref: '#components/responses/generic422', response: '422'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function createLink(string $id, string $linkedTaskId): JsonResponse
    {
        $command = new CreateTaskLinkCommand($id, $linkedTaskId);

        $this->commandBus->dispatch($command);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('/{id}/links/{linkedTaskId}/', name: 'deleteLink', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Delete link from another task',
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
            ),
            new OA\Parameter(
                ref: '#/components/parameters/linkedTaskId'
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
    public function deleteLink(string $id, string $linkedTaskId): JsonResponse
    {
        $command = new DeleteTaskLinkCommand($id, $linkedTaskId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }
}
