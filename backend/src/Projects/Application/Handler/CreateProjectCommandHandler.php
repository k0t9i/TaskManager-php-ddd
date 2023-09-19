<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\CreateProjectCommand;
use TaskManager\Projects\Application\Service\ProjectSaverInterface;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;

final readonly class CreateProjectCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectSaverInterface $saver,
        private AuthenticatorServiceInterface $authenticator,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CreateProjectCommand $command): void
    {
        $authUserId = $this->authenticator->getUserId();

        $project = Project::create(
            new ProjectId($command->id),
            new ProjectInformation(
                new ProjectName($command->name),
                new ProjectDescription($command->description),
                new ProjectFinishDate($command->finishDate)
            ),
            new ProjectOwner(
                $authUserId
            )
        );

        $newVersion = $this->saver->save($project, 0);
        $this->eventBus->dispatch($project->releaseEvents(), $newVersion);
    }
}
