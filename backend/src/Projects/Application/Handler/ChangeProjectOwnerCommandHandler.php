<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ChangeProjectOwnerCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\Repository\ProjectUserRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;

final readonly class ChangeProjectOwnerCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private ProjectFinderInterface $finder,
        private ProjectUserRepositoryInterface $userRepository,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ChangeProjectOwnerCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $project = $this->finder->find(new ProjectId($command->id));
        $user = $this->userRepository->findById(new ProjectUserId($command->userId));
        if (null === $user) {
            throw new UserDoesNotExistException($command->userId);
        }

        $project->changeOwner(
            new ProjectOwner($user->id),
            $currentUser->id
        );

        $this->repository->save($project);
        $this->eventBus->dispatch(...$project->releaseEvents());
    }
}
