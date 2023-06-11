<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Shared\Domain\Equatable;

final readonly class ProjectInformation implements Equatable
{
    public function __construct(
        public ProjectName $name,
        public ProjectDescription $description,
        public ProjectFinishDate $finishDate,
    ) {
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->name->equals($this->name)
            && $other->description->equals($this->description)
            && $other->finishDate->equals($this->finishDate);
    }
}
