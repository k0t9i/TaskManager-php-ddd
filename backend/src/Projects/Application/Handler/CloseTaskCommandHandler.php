<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\CloseTaskCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class CloseTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskFinderInterface $finder,
        private ProjectFinderInterface $projectFinder,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CloseTaskCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $task = $this->finder->find(new TaskId($command->id));
        $project = $this->projectFinder->find($task->getProjectId());

        $project->closeTask($task, $currentUser->id);

        $this->repository->save($task);
        $this->eventBus->dispatch($task->releaseEvents());
    }
}
