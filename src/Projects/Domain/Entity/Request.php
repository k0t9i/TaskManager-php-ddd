<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\ValueObject\PendingRequestStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectUserId;
use TaskManager\Projects\Domain\ValueObject\RequestChangeDate;
use TaskManager\Projects\Domain\ValueObject\RequestId;
use TaskManager\Projects\Domain\ValueObject\RequestStatus;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Hashable;

final class Request implements Equatable, Hashable
{
    public function __construct(
        private readonly RequestId $id,
        private readonly ProjectId $projectId,
        private readonly ProjectUserId $userId,
        private RequestStatus $status,
        private RequestChangeDate $changeDate
    ) {
    }

    public static function create(RequestId $id, ProjectId $projectId, ProjectUserId $userId): self
    {
        $status = new PendingRequestStatus();
        $changeDate = new RequestChangeDate();

        return new Request($id, $projectId, $userId, $status, $changeDate);
    }

    public function changeStatus(RequestStatus $status): void
    {
        $this->status->ensureCanBeChangedTo($status);
        $this->status = $status;
        $this->changeDate = new RequestChangeDate();
    }

    public function isPendingForUser(ProjectUserId $userId): bool
    {
        return $this->status->isPending() && $this->userId->equals($userId);
    }

    public function getId(): RequestId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    public function getStatus(): RequestStatus
    {
        return $this->status;
    }

    public function getUserId(): ProjectUserId
    {
        return $this->userId;
    }

    public function getChangeDate(): RequestChangeDate
    {
        return $this->changeDate;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->userId->equals($this->userId)
            && $other->status->equals($this->status)
            && $other->changeDate->equals($this->changeDate);
    }

    public function getHash(): string
    {
        return $this->id->value;
    }
}
