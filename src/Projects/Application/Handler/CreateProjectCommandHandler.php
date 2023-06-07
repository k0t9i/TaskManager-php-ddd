<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\CreateProjectCommand;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\Repository\ProjectUserRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;

final readonly class CreateProjectCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectRepositoryInterface     $projectRepository,
        private ProjectUserRepositoryInterface $userRepository,
        private IntegrationEventBusInterface   $eventBus,
        private AuthenticatorServiceInterface  $authenticator,
    ) {
    }

    public function __invoke(CreateProjectCommand $command): void
    {
        $projectUserId = new ProjectUserId($this->authenticator->getUserId());
        $projectUser = $this->userRepository->findById($projectUserId);

        if (null === $projectUser) {
            throw new UserDoesNotExistException($projectUserId->value);
        }

        $project = Project::create(
            new ProjectId($command->id),
            new ProjectInformation(
                new ProjectName($command->name),
                new ProjectDescription($command->description),
                new ProjectFinishDate($command->finishDate)
            ),
            new ProjectOwner(
                $projectUser->id
            )
        );

        $this->projectRepository->save($project);
        $this->eventBus->dispatch(...$project->releaseEvents());
    }
}
