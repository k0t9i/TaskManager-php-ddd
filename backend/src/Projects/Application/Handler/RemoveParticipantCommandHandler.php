<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\RemoveParticipantCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class RemoveParticipantCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private ProjectFinderInterface $finder,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface $eventBus,
    ) {
    }

    public function __invoke(RemoveParticipantCommand $command): void
    {
        $currentUser = $this->userExtractor->extract();
        $project = $this->finder->find(new ProjectId($command->projectId));

        $project->removeParticipant(
            new ProjectUserId($command->participantId),
            $currentUser->id
        );

        $this->repository->save($project);
        $this->eventBus->dispatch($project->releaseEvents());
    }
}
