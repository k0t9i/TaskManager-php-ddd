<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Collection;

use TaskManager\Projects\Domain\Exception\ProjectTaskDoesNotExistException;
use TaskManager\Projects\Domain\Exception\ProjectUserHasTaskException;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectTask;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class ProjectTaskCollection extends ManagedCollection
{
    public function ensureUserDoesNotHaveTask(ProjectUserId $userId, ProjectId $projectId): void
    {
        /** @var ProjectTask $task */
        foreach ($this->getItems() as $task) {
            if ($task->userId->equals($userId)) {
                throw new ProjectUserHasTaskException($userId->value, $projectId->value);
            }
        }
    }

    public function ensureProjectTaskExits(TaskId $id): void
    {
        if (!$this->exists($id->value)) {
            throw new ProjectTaskDoesNotExistException($id->value);
        }
    }

    /**
     * @return class-string
     */
    protected function supportClass(): string
    {
        return ProjectTask::class;
    }
}
