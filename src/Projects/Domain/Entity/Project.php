<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\Entity;

use TaskManager\Projects\Domain\Event\ProjectWasCreatedEvent;
use TaskManager\Projects\Domain\ValueObject\ActiveProjectStatus;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectInformation;
use TaskManager\Projects\Domain\ValueObject\ProjectOwner;
use TaskManager\Projects\Domain\ValueObject\ProjectStatus;
use TaskManager\Shared\Domain\Aggregate\AggregateRoot;
use TaskManager\Shared\Domain\Equatable;

final class Project extends AggregateRoot
{
    public function __construct(
        private readonly ProjectId $id,
        private ProjectInformation $information,
        private ProjectStatus $status,
        private ProjectOwner $owner
    ) {
    }

    public static function create(
        ProjectId $id,
        ProjectInformation $information,
        ProjectOwner $owner
    ): self {
        $status = new ActiveProjectStatus();
        $project = new self(
            $id,
            $information,
            $status,
            $owner
        );

        $project->registerEvent(new ProjectWasCreatedEvent(
            $id->value,
            $information->name->value,
            $information->description->value,
            $information->finishDate->getValue(),
            (string) $status->getScalar(),
            $owner->userId->value
        ));

        return $project;
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->id->equals($this->id)
            && $other->information->equals($this->information)
            && $other->status->equals($this->status)
            && $other->owner->equals($this->owner);
    }
}
