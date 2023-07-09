<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\CreateTaskCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\TaskBrief;
use TaskManager\Projects\Domain\ValueObject\TaskDescription;
use TaskManager\Projects\Domain\ValueObject\TaskFinishDate;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskInformation;
use TaskManager\Projects\Domain\ValueObject\TaskName;
use TaskManager\Projects\Domain\ValueObject\TaskOwner;
use TaskManager\Projects\Domain\ValueObject\TaskStartDate;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class CreateTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private CurrentUserExtractorInterface $userExtractor,
        private ProjectFinderInterface $finder,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CreateTaskCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $project = $this->finder->find(new ProjectId($command->projectId));

        $task = $project->createTask(
            new TaskId($command->id),
            new TaskInformation(
                new TaskName($command->name),
                new TaskBrief($command->brief),
                new TaskDescription($command->description),
                new TaskStartDate($command->startDate),
                new TaskFinishDate($command->finishDate)
            ),
            new TaskOwner(
                $currentUser->id
            )
        );

        $this->repository->save($task);
        $this->eventBus->dispatch(...$task->releaseEvents());
    }
}
