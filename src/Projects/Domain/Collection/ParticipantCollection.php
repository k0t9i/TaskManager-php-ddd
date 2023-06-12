<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Collection;

use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Shared\Domain\Collection\ManagedCollection;

final class ParticipantCollection extends ManagedCollection
{
    public function ensureUserIsParticipant(ProjectUserId $userId): void
    {
        if (!$this->exists($userId->value)) {
            throw new ProjectParticipantDoesNotExistException($userId->value);
        }
    }

    public function ensureUserIsNotParticipant(ProjectUserId $userId): void
    {
        if ($this->exists($userId->value)) {
            throw new UserIsAlreadyProjectParticipantException($userId->value);
        }
    }

    /**
     * @return class-string
     */
    protected function supportClass(): string
    {
        return Participant::class;
    }
}
