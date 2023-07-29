<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Subscriber;

use TaskManager\Projects\Application\Service\TaskFinderInterface;
use TaskManager\Projects\Domain\Event\ProjectTaskWasCreatedEvent;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Application\Bus\Event\DomainEventSubscriberInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventBusInterface;

final readonly class UndraftTaskOnProjectTaskCreated implements DomainEventSubscriberInterface
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskFinderInterface $finder,
        private IntegrationEventBusInterface $eventBus
    ) {
    }

    public function __invoke(ProjectTaskWasCreatedEvent $event): void
    {
        $task = $this->finder->find(new TaskId($event->taskId));

        $task->undraft();

        $this->repository->save($task);
        $this->eventBus->dispatch($task->releaseEvents());
    }
}
