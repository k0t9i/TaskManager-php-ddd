<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Handler;

use TaskManager\Projects\Application\Command\ChangeProjectInformationCommand;
use TaskManager\Projects\Application\Service\CurrentUserExtractorInterface;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Exception\ProjectDoesNotExistException;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectDescription;
use TaskManager\Projects\Domain\ValueObject\ProjectFinishDate;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectName;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Application\Bus\Command\CommandHandlerInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Domain\Exception\UserDoesNotExistException;

final readonly class ChangeProjectInformationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectRepositoryInterface    $projectRepository,
        private CurrentUserExtractorInterface $userExtractor,
        private IntegrationEventBusInterface  $eventBus,
    ) {
    }

    public function __invoke(ChangeProjectInformationCommand $command): void
    {
        $projectUser = $this->userExtractor->extract();

        $project = $this->projectRepository->findById(new ProjectId($command->id));
        if (null === $project) {
            throw new ProjectDoesNotExistException($command->id);
        }

        $project->changeInformation(
            $command->name ? new ProjectName($command->name) : null,
            $command->description ? new ProjectDescription($command->description) : null,
            $command->finishDate ? new ProjectFinishDate($command->finishDate) : null,
            $projectUser->id
        );

        $this->projectRepository->save($project);
        $this->eventBus->dispatch(...$project->releaseEvents());
    }
}
