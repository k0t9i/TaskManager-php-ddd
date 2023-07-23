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
use TaskManager\Projections\Application\Query\ProjectListQuery;
use TaskManager\Projections\Application\Query\ProjectParticipantQuery;
use TaskManager\Projections\Application\Query\ProjectQuery;
use TaskManager\Projections\Application\Query\ProjectRequestQuery;
use TaskManager\Projections\Application\Query\TaskListQuery;
use TaskManager\Projections\Infrastructure\DTO\ProjectListResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\ProjectParticipantResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\ProjectRequestResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\ProjectResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\TaskListResponseDTO;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Infrastructure\Criteria\QueryCriteriaFromRequestConverterInterface;
use TaskManager\Shared\Infrastructure\Criteria\RequestCriteriaDTO;
use TaskManager\Shared\Infrastructure\Paginator\PaginationResponseDTO;

#[AsController]
#[Route('/api/projects', name: 'project.')]
#[Areas(['default'])]
final readonly class ProjectProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private QueryCriteriaFromRequestConverterInterface $converter
    ) {
    }

    #[Route('/', name: 'getAll', methods: ['GET'])]
    #[OA\Get(
        description: 'Get project list',
        tags: [
            'project',
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of user projects with pagination',
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
                                        ref: new Model(type: ProjectListResponseDTO::class)
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAll(RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new ProjectListQuery($this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => ProjectListResponseDTO::createFromProjections($items)
        ));
    }

    #[Route('/{id}/', name: 'get', methods: ['GET'])]
    #[OA\Get(
        description: 'Get project info',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Project info',
                content: new OA\JsonContent(
                    ref: new Model(type: ProjectResponseDTO::class)
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
        $project = $this->queryBus->dispatch(new ProjectQuery($id));

        return new JsonResponse(ProjectResponseDTO::createFromProjection($project));
    }

    #[Route('/{id}/requests/', name: 'getAllRequests', methods: ['GET'])]
    #[OA\Get(
        description: 'Get all requests to a project',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of project requests with pagination',
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
                                        ref: new Model(type: ProjectRequestResponseDTO::class)
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
    public function getAllRequests(string $id, RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new ProjectRequestQuery($id, $this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => ProjectRequestResponseDTO::createFromProjections($items)
        ));
    }

    #[Route('/{id}/tasks/', name: 'getAllTasks', methods: ['GET'])]
    #[OA\Get(
        description: 'Get task list',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of project tasks with pagination',
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
                                        ref: new Model(type: TaskListResponseDTO::class)
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAllTasks(string $id, RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new TaskListQuery($id, $this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => TaskListResponseDTO::createFromProjections($items)
        ));
    }

    #[Route('/{id}/participants/', name: 'getAllParticipants', methods: ['GET'])]
    #[OA\Get(
        description: 'Get all project participants',
        tags: [
            'project',
        ],
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/projectId'
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of project participants with pagination',
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
                                        ref: new Model(type: ProjectParticipantResponseDTO::class)
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
    public function getAllParticipants(string $id, RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new ProjectParticipantQuery($id, $this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => ProjectParticipantResponseDTO::createFromProjections($items)
        ));
    }
}
