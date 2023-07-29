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
use TaskManager\Projections\Application\Query\UserProfileQuery;
use TaskManager\Projections\Application\Query\UserProjectQuery;
use TaskManager\Projections\Application\Query\UserRequestQuery;
use TaskManager\Projections\Infrastructure\DTO\ProjectListResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\UserRequestResponseDTO;
use TaskManager\Projections\Infrastructure\DTO\UserResponseDTO;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Infrastructure\Criteria\QueryCriteriaFromRequestConverterInterface;
use TaskManager\Shared\Infrastructure\Criteria\RequestCriteriaDTO;
use TaskManager\Shared\Infrastructure\Paginator\PaginationResponseDTO;

#[AsController]
#[Route('/api/users', name: 'user.')]
#[Areas(['default'])]
final readonly class UserProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private QueryCriteriaFromRequestConverterInterface $converter
    ) {
    }

    #[Route('/', name: 'getInfo', methods: ['GET'])]
    #[OA\Get(
        description: 'Get info about own profile',
        tags: [
            'user',
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Profile info',
                content: new OA\JsonContent(
                    ref: new Model(type: UserResponseDTO::class)
                )
            ),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getInfo(): JsonResponse
    {
        $user = $this->queryBus->dispatch(new UserProfileQuery());

        return new JsonResponse(UserResponseDTO::createFromProjection($user));
    }

    #[Route('/requests/', name: 'getAllRequests', methods: ['GET'])]
    #[OA\Get(
        description: 'Get all user requests',
        tags: [
            'user',
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of user requests with pagination',
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
                                        ref: new Model(type: UserRequestResponseDTO::class)
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
    public function getAllRequests(RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new UserRequestQuery($this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => UserRequestResponseDTO::createFromProjections($items)
        ));
    }

    #[Route('/projects/', name: 'getAllProjects', methods: ['GET'])]
    #[OA\Get(
        description: 'Get all user projects',
        tags: [
            'user',
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
            new OA\Response(ref: '#components/responses/generic403', response: '403'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getAllProjects(RequestCriteriaDTO $criteria): JsonResponse
    {
        /** @var Pagination $pagination */
        $pagination = $this->queryBus->dispatch(
            new UserProjectQuery($this->converter->convert($criteria))
        );

        return new JsonResponse(PaginationResponseDTO::createFromPagination(
            $pagination,
            fn (array $items) => ProjectListResponseDTO::createList($items)
        ));
    }
}
