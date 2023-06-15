<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ChangeTaskInformationCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class ChangeTaskInformationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskFinderInterface $finder,
        private ProjectFinderInterface $projectFinder,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ChangeTaskInformationCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $task = $this->finder->find(new TaskId($command->id));
        $project = $this->projectFinder->find($task->getProjectId());

        $project->changeTaskInformation(
            $task,
            $command->name ? new TaskName($command->name) : null,
            $command->name ? new TaskBrief($command->brief) : null,
            $command->description ? new TaskDescription($command->description) : null,
            $command->finishDate ? new TaskStartDate($command->startDate) : null,
            $command->finishDate ? new TaskFinishDate($command->finishDate) : null,
            $currentUser->id
        );

        $this->repository->save($task);
        $this->eventBus->dispatch(...$task->releaseEvents());
    }
}
