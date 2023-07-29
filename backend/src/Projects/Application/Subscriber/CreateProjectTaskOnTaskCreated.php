<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Subscriber;

use TaskManager\Projects\Application\Service\ProjectFinderInterface;
use TaskManager\Projects\Domain\Event\TaskWasCreatedEvent;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class CreateProjectTaskOnTaskCreated implements DomainEventSubscriberInterface
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private ProjectFinderInterface $finder,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(TaskWasCreatedEvent $event): void
    {
        $project = $this->finder->find(new ProjectId($event->projectId));

        $project->addProjectTask(
            new TaskId($event->getAggregateId()),
            new ProjectUserId($event->ownerId)
        );

        $this->repository->save($project);
        $this->eventBus->dispatch($project->releaseEvents());
    }
}
