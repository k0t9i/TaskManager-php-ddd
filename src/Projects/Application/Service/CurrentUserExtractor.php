<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\Repository\ProjectUserRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectUser;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;

final readonly class CurrentUserExtractor implements CurrentUserExtractorInterface
{
    public function __construct(
        private ProjectUserRepositoryInterface $repository,
        private AuthenticatorServiceInterface  $authenticator,
    ) {
    }

    public function extract(): ProjectUser
    {
        $projectUserId = new ProjectUserId($this->authenticator->getUserId());
        $projectUser = $this->repository->findById($projectUserId);

        if (null === $projectUser) {
            throw new UserDoesNotExistException($projectUserId->value);
        }

        return $projectUser;
    }
}
