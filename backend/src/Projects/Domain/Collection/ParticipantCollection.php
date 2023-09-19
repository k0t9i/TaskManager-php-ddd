<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Collection;

use TaskManager\Projects\Domain\Exception\ProjectParticipantDoesNotExistException;
use TaskManager\Projects\Domain\Exception\UserIsAlreadyProjectParticipantException;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Shared\Domain\Collection\ManagedCollection;
use TaskManager\Shared\Domain\ValueObject\UserId;

final class ParticipantCollection extends ManagedCollection
{
    public function ensureUserIsParticipant(UserId $userId): void
    {
        if (!$this->exists($userId->value)) {
            throw new ProjectParticipantDoesNotExistException($userId->value);
        }
    }

    public function ensureUserIsNotParticipant(UserId $userId): void
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
