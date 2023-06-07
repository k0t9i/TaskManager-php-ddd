<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ActivateProjectCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class ActivateProjectCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectRepositoryInterface    $repository,
        private ProjectFinderInterface        $finder,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface  $eventBus,
    ) {
    }

    public function __invoke(ActivateProjectCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $project = $this->finder->find(new ProjectId($command->id));

        $project->activate($currentUser->id);

        $this->repository->save($project);
        $this->eventBus->dispatch(...$project->releaseEvents());
    }
}
