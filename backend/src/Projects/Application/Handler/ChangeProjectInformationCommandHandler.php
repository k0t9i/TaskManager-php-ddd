<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ChangeProjectInformationCommand;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Application\Service\ProjectSaverInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;

final readonly class ChangeProjectInformationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectSaverInterface $saver,
        private ProjectFinderInterface $finder,
        private AuthenticatorServiceInterface $authenticator,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ChangeProjectInformationCommand $command): int
    {
        $authUserId = $this->authenticator->getUserId();
        $project = $this->finder->find(new ProjectId($command->id));

        $project->changeInformation(
            new ProjectInformation(
                new ProjectName($command->name),
                new ProjectDescription($command->description),
                new ProjectFinishDate($command->finishDate),
            ),
            $authUserId
        );

        $version = (int) $command->version;

        $events = $project->releaseEvents();
        if (0 !== count($events)) {
            $version = $this->saver->save($project, $version);
            $this->eventBus->dispatch($events, $version);
        }

        return $version;
    }
}
