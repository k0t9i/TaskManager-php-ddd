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
use TaskManager\Projections\Application\Query\TaskLinkQuery;
use TaskManager\Projections\Application\Query\TaskQuery;
use TaskManager\Projections\Infrastructure\DTO\TaskLinkResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\TaskResponseDTO;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Infrastructure\Criteria\QueryCriteriaFromRequestConverterInterface;
use TaskManager\Shared\Infrastructure\Criteria\RequestCriteriaDTO;
use TaskManager\Shared\Infrastructure\Paginator\PaginationResponseDTO;

#[AsController]
#[Route('/api/tasks', name: 'task.')]
#[Areas(['default'])]
final readonly class TaskProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private QueryCriteriaFromRequestConverterInterface $converter
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

    #[Route('/{id}/links/', name: 'getAllLinks', methods: ['GET'])]
    #[OA\Get(
        description: 'Get all task links',
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
                description: 'List of task links with pagination',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: '#components/schemas/pagination'
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'items',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: new Model(type: TaskLinkResponseDTO::class)
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAllLinks(string $id, RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new TaskLinkQuery($id, $this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => TaskLinkResponseDTO::createFromProjections($items)
        ));
    }
}
