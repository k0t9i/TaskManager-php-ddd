<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ChangeTaskInformationCommand;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Application\Service\TaskSaverInterface;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;

final readonly class ChangeTaskInformationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskSaverInterface $saver,
        private TaskFinderInterface $finder,
        private ProjectFinderInterface $projectFinder,
        private AuthenticatorServiceInterface $authenticator,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ChangeTaskInformationCommand $command): int
    {
        $authUserId = $this->authenticator->getUserId();
        $task = $this->finder->find(new TaskId($command->id));
        $project = $this->projectFinder->find($task->getProjectId());

        $project->changeTaskInformation(
            $task,
            new TaskInformation(
                new TaskName($command->name),
                new TaskBrief($command->brief),
                new TaskDescription($command->description),
                new TaskStartDate($command->startDate),
                new TaskFinishDate($command->finishDate),
            ),
            $authUserId
        );

        $version = (int) $command->version;

        $events = $task->releaseEvents();
        if (0 !== count($events)) {
            $version = $this->saver->save($task, $version);
            $this->eventBus->dispatch($events, $version);
        }

        return $version;
    }
}
