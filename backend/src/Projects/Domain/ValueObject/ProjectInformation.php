<?php

declare(strict_types=1);

namespace TaskManager\Projects\Domain\ValueObject;

use TaskManager\Projects\Domain\Exception\TaskFinishDateIsGreaterThanProjectFinishDateException;
use TaskManager\Projects\Domain\Exception\TaskStartDateIsGreaterThanProjectFinishDateException;
use TaskManager\Shared\Domain\Equatable;

final readonly class ProjectInformation implements Equatable
{
    public function __construct(
        public ProjectName $name,
        public ProjectDescription $description,
        public ProjectFinishDate $finishDate,
    ) {
    }

    public function ensureIsFinishDateGreaterThanTaskDates(?TaskStartDate $startDate, ?TaskFinishDate $finishDate): void
    {
        if (null !== $startDate && $startDate->isGreaterThan($this->finishDate)) {
            throw new TaskStartDateIsGreaterThanProjectFinishDateException($this->finishDate->getValue(), $startDate->getValue())
            ;
        }
        if (null !== $finishDate && $finishDate->isGreaterThan($this->finishDate)) {
            throw new TaskFinishDateIsGreaterThanProjectFinishDateException($this->finishDate->getValue(), $finishDate->getValue());
        }
    }

    public function equals(Equatable $other): bool
    {
        return $other instanceof self
            && $other->name->equals($this->name)
            && $other->description->equals($this->description)
            && $other->finishDate->equals($this->finishDate);
    }
}
