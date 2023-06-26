<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Service;

use TaskManager\Projections\Domain\Entity\UserProjection;
use TaskManager\Projections\Domain\Repository\UserProjectionRepositoryInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;

final readonly class CurrentUserExtractor implements CurrentUserExtractorInterface
{
    public function __construct(
        private UserProjectionRepositoryInterface $repository,
        private AuthenticatorServiceInterface $authenticator,
    ) {
    }

    public function extract(): UserProjection
    {
        $userId = $this->authenticator->getUserId();
        $user = $this->repository->findById($userId);

        if (null === $user) {
            throw new UserDoesNotExistException($userId);
        }

        return $user;
    }
}
