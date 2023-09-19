<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Collection;

use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\Exception\UserAlreadyHasPendingRequestException;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Shared\Domain\Collection\ManagedCollection;
use TaskManager\Shared\Domain\ValueObject\UserId;

final class RequestCollection extends ManagedCollection
{
    public function ensureUserDoesNotHavePendingRequest(UserId $userId, ProjectId $projectId): void
    {
        $request = null;

        /** @var Request $item */
        foreach ($this->getItems() as $item) {
            if ($item->isPendingForUser($userId)) {
                $request = $item;
                break;
            }
        }

        if (null !== $request) {
            throw new UserAlreadyHasPendingRequestException($userId->value, $projectId->value);
        }
    }

    /**
     * @return class-string
     */
    protected function supportClass(): string
    {
        return Request::class;
    }
}
