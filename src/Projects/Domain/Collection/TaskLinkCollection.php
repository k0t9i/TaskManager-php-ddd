<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Collection;

use TaskManager\Projects\Domain\Exception\TaskLinkAlreadyExistsException;
use TaskManager\Projects\Domain\Exception\TaskLinkDoesNotExistException;
use TaskManager\Projects\Domain\ValueObject\TaskLink;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class TaskLinkCollection extends ManagedCollection
{
    public function ensureTaskLinkExists(TaskLink $link): void
    {
        if (!$this->exists($link->getHash())) {
            throw new TaskLinkDoesNotExistException($link->linkedTaskId->value, $link->taskId->value);
        }
    }

    public function ensureTaskLinkDoesNotExist(TaskLink $link): void
    {
        if ($this->exists($link->getHash())) {
            throw new TaskLinkAlreadyExistsException($link->linkedTaskId->value, $link->taskId->value);
        }
    }

    /**
     * @return class-string
     */
    protected function supportClass(): string
    {
        return TaskLink::class;
    }
}
