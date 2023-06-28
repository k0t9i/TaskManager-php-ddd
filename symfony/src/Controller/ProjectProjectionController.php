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
use TaskManager\Projections\Application\Query\ProjectQuery;
use TaskManager\Projections\Application\Query\ProjectRequestQuery;
use TaskManager\Projections\Infrastructure\DTO\ProjectListResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\ProjectRequestResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\ProjectResponseDTO;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;

#[AsController]
#[Route('/api/projects', name: 'project.')]
#[Areas(['default'])]
final readonly class ProjectProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus
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
                description: 'List of user projects',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: ProjectListResponseDTO::class)
                    )
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAll(): JsonResponse
    {
        $projects = $this->queryBus->dispatch(new ProjectListQuery());

        return new JsonResponse(ProjectListResponseDTO::createFromProjections($projects));
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
                description: 'List of project requests',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: ProjectRequestResponseDTO::class)
                    )
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAllRequests(string $id): JsonResponse
    {
        $requests = $this->queryBus->dispatch(new ProjectRequestQuery($id));

        return new JsonResponse(ProjectRequestResponseDTO::createFromProjections($requests));
    }
}
