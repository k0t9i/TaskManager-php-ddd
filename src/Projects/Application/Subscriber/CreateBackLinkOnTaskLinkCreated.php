<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Subscriber;

use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Domain\Event\TaskLinkWasCreated;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class CreateBackLinkOnTaskLinkCreated implements DomainEventSubscriberInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskFinderInterface $finder,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(TaskLinkWasCreated $event): void
    {
        $task = $this->finder->find(new TaskId($event->linkedTaskId));

        $task->createBackLink(
            new TaskId($event->getAggregateId()),
            new ProjectUserId($event->getPerformerId())
        );

        $this->repository->save($task);
        $this->eventBus->dispatch(...$task->releaseEvents());
    }
}
