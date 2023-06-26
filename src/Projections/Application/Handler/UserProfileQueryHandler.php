<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Handler;

use TaskManager\Projections\Application\Query\UserProfileQuery;
use TaskManager\Projections\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Shared\Application\Bus\Query\QueryHandlerInterface;

final readonly class UserProfileQueryHandler implements QueryHandlerInterface
{
    public function __construct(private CurrentUserExtractorInterface $userExtractor)
    {
    }

    public function __invoke(UserProfileQuery $query): UserProjection
    {
        return $this->userExtractor->extract();
    }
}
