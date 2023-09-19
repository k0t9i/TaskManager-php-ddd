<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Subscriber;

use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Domain\Event\ProjectTaskWasClosedEvent;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;
use TaskManager\Shared\Domain\ValueObject\UserId;

final readonly class CloseTaskOnProjectTaskClosed implements DomainEventSubscriberInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskFinderInterface $finder,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(ProjectTaskWasClosedEvent $event): void
    {
        $task = $this->finder->find(new TaskId($event->taskId));

        $task->closeAsNeeded(new UserId($event->getPerformerId()));

        $this->repository->save($task);
        $this->eventBus->dispatch($task->releaseEvents());
    }
}
