<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Projections\Application\Query\UserProfileQuery;
use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;

#[AsController]
#[Route('/api/users', name: 'user.')]
#[Areas(['default'])]
final readonly class UserProjectionController
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {
    }

    #[Route('/', name: 'getInfo', methods: ['GET'])]
    #[OA\Get(
        description: 'Get info about own profile',
        tags: [
            'user',
        ],
        responses: [
            new OA\Response(ref: '#components/responses/generic200', response: '200'),
            new OA\Response(ref: '#components/responses/generic401', response: '401'),
            new OA\Response(ref: '#components/responses/generic404', response: '404'),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getInfo(): JsonResponse
    {
        $user = $this->queryBus->dispatch(new UserProfileQuery());

        return new JsonResponse($user);
    }
}
