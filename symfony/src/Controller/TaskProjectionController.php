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
use TaskManager\Projections\Application\Query\TaskQuery;
use TaskManager\Projections\Infrastructure\DTO\TaskResponseDTO;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;

#[AsController]
#[Route('/api/tasks', name: 'task.')]
#[Areas(['default'])]
final readonly class TaskProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {
    }

    #[Route('/{id}/', name: 'get', methods: ['GET'])]
    #[OA\Get(
        description: 'Get task info',
        tags: [
            'task',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/taskId'
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Task info',
                content: new OA\JsonContent(
                    ref: new Model(type: TaskResponseDTO::class)
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function get(string $id): JsonResponse
    {
        $task = $this->queryBus->dispatch(new TaskQuery($id));

        return new JsonResponse(TaskResponseDTO::createFromProjection($task));
    }
}
